# main.py
from fastapi import FastAPI, UploadFile, File
from fastapi.responses import JSONResponse
import pandas as pd
import numpy as np
import joblib
import io
import os
import math
from typing import List

app = FastAPI(title="API Predicción nivel_regla")
MODEL_PATH = os.getenv("MODEL_PATH", "arbol_nivel_regla.joblib")

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

def transform_like_training(df: pd.DataFrame, feat_cols_expected: List[str]) -> pd.DataFrame:
    """
    Preprocesa exactamente como el entrenamiento (train_arbol_nivel_regla_v3):
      - Construye 'ultimo_abono_ts' a partir de 'ultimo_abono_fecha' (formato DD/MM/YYYY).
      - Elimina el texto de fecha (no se usa en el modelo).
      - Convierte el resto a numérico y rellena NaN con 0.
      - Alinea columnas y orden a feat_cols_expected.
    """
    X = pd.DataFrame(index=df.index)

    # 1) generar 'ultimo_abono_ts' desde 'ultimo_abono_fecha' (DD/MM/YYYY)
    if 'ultimo_abono_fecha' in df.columns:
        parsed = pd.to_datetime(df['ultimo_abono_fecha'], format="%d/%m/%Y", errors='coerce')
        ts = (parsed.view('int64') / 1e9).where(parsed.notna(), np.nan)  # segundos desde epoch
        X['ultimo_abono_ts'] = ts
    else:
        # Si no viene la fecha, dejamos el ts en 0 (o NaN -> luego fillna(0))
        X['ultimo_abono_ts'] = 0.0

    # 2) copiar columnas numéricas usadas en el modelo (excepto 'ultimo_abono_ts' que ya hicimos)
    #    Estas son las que definimos en el entrenamiento v3:
    #    'dias_aplazo','total_pedidos','pedidos_cerrados','total_creditos',
    #    'creditos_activos','creditos_vencidos','creditos_liquidados',
    #    'total_abonado','promedio_abonos'
    for col in df.columns:
        if col == 'ultimo_abono_fecha':
            continue  # no la usamos directamente
        if col == 'ultimo_abono_ts':
            continue  # ya la construimos
        if col in feat_cols_expected:
            X[col] = df[col]

    # 3) A numérico + NaN -> 0
    for c in X.columns:
        if not pd.api.types.is_numeric_dtype(X[c]):
            X[c] = pd.to_numeric(X[c], errors='coerce')
    X = X.fillna(0)

    # 4) Asegurar todas las columnas esperadas y en el orden exacto
    for col in feat_cols_expected:
        if col not in X.columns:
            X[col] = 0
    X = X[feat_cols_expected]

    return X

@app.on_event("startup")
def load_model():
    if not os.path.exists(MODEL_PATH):
        raise RuntimeError(f"No se encontró el modelo: {MODEL_PATH}")
    bundle = joblib.load(MODEL_PATH)
    for k in ('model', 'label_encoder', 'feature_columns'):
        if k not in bundle:
            raise RuntimeError("El joblib debe tener 'model','label_encoder','feature_columns'")
    app.state.bundle = bundle

@app.post("/predict-csv")
async def predict_csv(file: UploadFile = File(...)):
    # Validación básica del archivo
    if not file.filename.lower().endswith(".csv"):
        return JSONResponse(status_code=400, content={"ok": False, "error": "Sube un archivo .csv"})

    # Leer CSV
    try:
        content = await file.read()
        df_in = pd.read_csv(io.BytesIO(content))
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"CSV inválido: {e}"})

    # Preprocesar exactamente como en el entrenamiento
    try:
        bundle = app.state.bundle
        X = transform_like_training(df_in, bundle['feature_columns'])
    except Exception as e:
        return JSONResponse(status_code=400, content={"ok": False, "error": f"Error de preprocesado: {e}"})

    # Predecir
    try:
        y_pred = bundle['model'].predict(X)
        labels = bundle['label_encoder'].inverse_transform(y_pred)
    except Exception as e:
        return JSONResponse(status_code=500, content={"ok": False, "error": f"Error al predecir: {e}"})

    # Respuesta JSON friendly (sin numpy scalars)
    data = []
    for i, lab in enumerate(labels):
        item = {"nivel_regla_predicho": str(lab)}
        if 'id_user' in df_in.columns:
            item["id_user"] = to_native(df_in.iloc[i].get('id_user'))
        if 'nombre_usuario' in df_in.columns:
            item["nombre_usuario"] = str(df_in.iloc[i].get('nombre_usuario'))
        data.append(item)

    return JSONResponse(content={"ok": True, "count": int(len(data)), "data": data})
