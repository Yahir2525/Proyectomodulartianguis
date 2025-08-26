# main_inputs9.py
from fastapi import FastAPI, UploadFile, File
from fastapi.responses import JSONResponse
import pandas as pd
import numpy as np
import joblib
import io
import os
import math
from typing import List

"""
API para predecir 'nivel_regla' con las MISMAS entradas usadas en el entrenamiento:
INPUTS EXACTOS:
  - dias_aplazo
  - total_creditos
  - creditos_activos
  - creditos_vencidos
  - creditos_liquidados
  - total_abonos
  - total_abonado
  - promedio_abonos
  - ultimo_abono_fecha

OUTPUT:
  - nivel_regla  (string)

Notas:
- La fecha se transforma internamente a 'ultimo_abono_ts' para el modelo,
  replicando el preprocesamiento del script de entrenamiento v3 (inputs9).
- No se calcula 'ratio_liquidados' ni ninguna otra feature extra.
- Se reordena exactamente según 'feature_columns' del joblib.
"""

app = FastAPI(title="API Predicción nivel_regla (inputs9)")
MODEL_PATH = os.getenv("MODEL_PATH", "arbol_nivel_regla.joblib")

REQUIRED_COLUMNS = [
    "dias_aplazo",
    "total_creditos",
    "creditos_activos",
    "creditos_vencidos",
    "creditos_liquidados",
    "total_abonos",
    "total_abonado",
    "promedio_abonos",
    "ultimo_abono_fecha",
]

def to_native(v):
    """Convierte numpy/pandas scalars a tipos Python JSON-compatibles."""
    if v is None:
        return None
    if isinstance(v, (np.integer,)):
        return int(v)
    if isinstance(v, (np.floating,)):
        f = float(v)
        return None if math.isnan(f) else f
    if isinstance(v, (np.bool_,)):
        return bool(v)
    return v  # str/int/float/bool nativos OK

def _to_unix_seconds(parsed: pd.Series) -> pd.Series:
    """
    Convierte un Series de timestamps pandas a segundos (float) desde epoch.
    Copia la lógica tolerante del entrenamiento.
    """
    try:
        return (parsed.view("int64") // 10**9).astype(float)
    except Exception:
        try:
            return (parsed.astype("int64") // 10**9).astype(float)
        except Exception:
            epoch = pd.Timestamp("1970-01-01")
            return ((parsed - epoch) // pd.Timedelta(seconds=1)).astype(float)

def _parse_fecha_like_training(series: pd.Series) -> pd.Series:
    """Parsea fecha como en el script de entrenamiento:
       - intenta %d/%m/%Y
       - fallback dayfirst=True
    """
    parsed = pd.to_datetime(series, format="%d/%m/%Y", errors="coerce")
    if parsed.isna().any():
        alt = pd.to_datetime(series, dayfirst=True, errors="coerce")
        parsed = parsed.fillna(alt)
    return parsed

def transform_like_training(df: pd.DataFrame, feat_cols_expected: List[str]) -> pd.DataFrame:
    """
    Preprocesa EXACTAMENTE como el entrenamiento inputs9:
      - Verifica columnas requeridas (permite columnas adicionales).
      - Construye 'ultimo_abono_ts' desde 'ultimo_abono_fecha'.
      - Elimina la fecha textual, conserva únicamente numéricos + ts.
      - Convierte todo a numérico y NaN->0.
      - Reordena/Completa columnas según feat_cols_expected.
    """
    missing = [c for c in REQUIRED_COLUMNS if c not in df.columns]
    if missing:
        raise KeyError(f"Faltan columnas requeridas: {missing}")

    X = pd.DataFrame(index=df.index)

    # Copiar columnas numéricas usadas en entrenamiento (excepto la fecha)
    for c in [
        "dias_aplazo",
        "total_creditos",
        "creditos_activos",
        "creditos_vencidos",
        "creditos_liquidados",
        "total_abonos",
        "total_abonado",
        "promedio_abonos",
    ]:
        X[c] = pd.to_numeric(df[c], errors="coerce")

    # Parsear fecha -> timestamp
    parsed = _parse_fecha_like_training(df["ultimo_abono_fecha"])
    X["ultimo_abono_ts"] = _to_unix_seconds(parsed)

    # A numérico + NaN->0
    for c in X.columns:
        X[c] = pd.to_numeric(X[c], errors="coerce")
    X = X.fillna(0)

    # Completar/reordenar exactamente como espera el modelo
    for c in feat_cols_expected:
        if c not in X.columns:
            X[c] = 0
    X = X[feat_cols_expected]
    return X

@app.on_event("startup")
def load_model():
    if not os.path.exists(MODEL_PATH):
        raise RuntimeError(f"No se encontró el modelo: {MODEL_PATH}")
    bundle = joblib.load(MODEL_PATH)
    for k in ("model", "label_encoder", "feature_columns"):
        if k not in bundle:
            raise RuntimeError("El joblib debe tener 'model','label_encoder','feature_columns'")
    app.state.bundle = bundle

@app.get("/health")
def health():
    return {"ok": True, "model_loaded": hasattr(app.state, "bundle")}

@app.get("/model-info")
def model_info():
    """
    Útil para depurar: ver qué columnas espera el modelo y su cantidad.
    """
    b = getattr(app.state, "bundle", None)
    if not b:
        return JSONResponse(status_code=500, content={"ok": False, "error": "Modelo no cargado"})
    return {
        "ok": True,
        "n_features": len(b["feature_columns"]),
        "feature_columns": b["feature_columns"],
        "required_input_columns": REQUIRED_COLUMNS,
    }

@app.post("/predict-csv")
async def predict_csv(file: UploadFile = File(...)):
    # Validación de extensión
    if not file.filename.lower().endswith(".csv"):
        return JSONResponse(status_code=400, content={"ok": False, "error": "Sube un archivo .csv"})

    # Leer CSV
    try:
        content = await file.read()
        df_in = pd.read_csv(io.BytesIO(content))
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"CSV inválido: {e}"})

    # Preprocesar como en entrenamiento
    try:
        bundle = app.state.bundle
        X = transform_like_training(df_in, bundle["feature_columns"])
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"Error de preprocesado: {e}"})

    # Predecir
    try:
        y_pred = bundle["model"].predict(X)
        labels = bundle["label_encoder"].inverse_transform(y_pred)
    except Exception as e:
        return JSONResponse(status_code=500, content={"ok": False, "error": f"Error al predecir: {e}"})

    # Respuesta: MISMA salida pedida -> 'nivel_regla'
    data = []
    for i, lab in enumerate(labels):
        item = {"nivel_regla": str(lab)}  # clave exacta solicitada
        # Passthrough opcional de identificadores si vienen en el CSV:
        if "id_user" in df_in.columns:
            item["id_user"] = to_native(df_in.iloc[i].get("id_user"))
        if "nombre_usuario" in df_in.columns:
            item["nombre_usuario"] = str(df_in.iloc[i].get("nombre_usuario"))
        data.append(item)

    return JSONResponse(content={"ok": True, "count": int(len(data)), "data": data})
