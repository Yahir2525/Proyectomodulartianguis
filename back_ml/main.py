# main_inputs_v4.py
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
API para predecir 'nivel_regla' con las MISMAS entradas usadas en el entrenamiento v4:
INPUTS EXACTOS:
  - dias_aplazo
  - total_creditos
  - creditos_activos
  - creditos_vencidos
  - creditos_liquidados
  - saldo_credito

OUTPUT:
  - nivel_regla (string)

Notas:
- No se usan columnas de abonos ni fechas.
- Se reordena exactamente según 'feature_columns' del joblib.
"""

app = FastAPI(title="API Predicción nivel_regla (inputs_v4)")
MODEL_PATH = os.getenv("MODEL_PATH", "arbol_nivel_regla.joblib")

REQUIRED_COLUMNS = [
    "dias_aplazo",
    "total_creditos",
    "creditos_activos",
    "creditos_vencidos",
    "creditos_liquidados",
    "saldo_credito",
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
    return v

def transform_like_training(df: pd.DataFrame, feat_cols_expected: List[str]) -> pd.DataFrame:
    """
    Preprocesa EXACTAMENTE como el entrenamiento v4:
      - Verifica columnas requeridas.
      - Convierte todo a numérico y NaN->0.
      - Reordena/Completa columnas según feat_cols_expected.
    """
    missing = [c for c in REQUIRED_COLUMNS if c not in df.columns]
    if missing:
        raise KeyError(f"Faltan columnas requeridas: {missing}")

    X = df[REQUIRED_COLUMNS].copy()

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
    """Ver qué columnas espera el modelo y su cantidad."""
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
    if not file.filename.lower().endswith(".csv"):
        return JSONResponse(status_code=400, content={"ok": False, "error": "Sube un archivo .csv"})

    try:
        content = await file.read()
        df_in = pd.read_csv(io.BytesIO(content))
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"CSV inválido: {e}"})

    try:
        bundle = app.state.bundle
        X = transform_like_training(df_in, bundle["feature_columns"])
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"Error de preprocesado: {e}"})

    try:
        y_pred = bundle["model"].predict(X)
        labels = bundle["label_encoder"].inverse_transform(y_pred)
    except Exception as e:
        return JSONResponse(status_code=500, content={"ok": False, "error": f"Error al predecir: {e}"})

    data = []
    for i, lab in enumerate(labels):
        item = {"nivel_regla": str(lab)}
        if "id_user" in df_in.columns:
            item["id_user"] = to_native(df_in.iloc[i].get("id_user"))
        if "nombre_usuario" in df_in.columns:
            item["nombre_usuario"] = str(df_in.iloc[i].get("nombre_usuario"))
        data.append(item)

    return JSONResponse(content={"ok": True, "count": int(len(data)), "data": data})
