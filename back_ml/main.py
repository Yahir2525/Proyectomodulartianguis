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

def select_feature_range(df: pd.DataFrame, start_col: str, end_col: str) -> pd.DataFrame:
    """Selecciona columnas por rango (inclusive) siguiendo el orden del CSV."""
    cols = list(df.columns)
    if start_col not in cols or end_col not in cols:
        raise KeyError(f"Faltan columnas: '{start_col}' o '{end_col}'")
    i0, i1 = cols.index(start_col), cols.index(end_col)
    return df.iloc[:, min(i0, i1):max(i0, i1) + 1].copy()

def encode_genero(series: pd.Series) -> pd.Series:
    """Mapeo simple para género en tus datos (H/M, con tolerancia a variantes)."""
    s = series.astype(str).str.strip().str.upper()
    mapa = {
        'H': 1, 'HOMBRE': 1, 'MASCULINO': 1,
        'M': 0, 'MUJER': 0, 'F': 0, 'FEMENINO': 0
    }
    return s.map(mapa)

def transform_like_training(df: pd.DataFrame, feat_cols_expected: List[str]) -> pd.DataFrame:
    """
    Replica tu entrenamiento:
      - subset: dias_aplazo..ultimo_abono_fecha
      - fecha -> 'ultimo_abono_ts' (segundos) y drop de 'ultimo_abono_fecha'
      - encode genero
      - to_numeric + fillna(0)
      - alinear columnas al orden guardado en el modelo
    """
    # 1) subset por rango
    X = select_feature_range(df, 'dias_aplazo', 'ultimo_abono_fecha')

    # 2) fecha -> timestamp (segundos desde epoch)
    if 'ultimo_abono_fecha' in X.columns:
        parsed = pd.to_datetime(X['ultimo_abono_fecha'], errors='coerce')
        ts = parsed.astype('int64')  # ns desde epoch; NaT -> sentinel
        ts = ts.where(parsed.notna(), np.nan) / 1e9  # a segundos y NaT -> NaN
        X['ultimo_abono_ts'] = ts
        X = X.drop(columns=['ultimo_abono_fecha'])

    # 3) codificar genero
    if 'genero' in X.columns:
        X['genero'] = encode_genero(X['genero'])

    # 4) a numérico + NaN -> 0
    for c in X.columns:
        if not pd.api.types.is_numeric_dtype(X[c]):
            X[c] = pd.to_numeric(X[c], errors='coerce')
    X = X.fillna(0)

    # 5) asegurar columnas y orden exactamente como en el entrenamiento
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
