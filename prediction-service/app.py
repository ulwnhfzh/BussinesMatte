from __future__ import annotations

from collections import Counter
from datetime import date, datetime, timedelta, timezone
from typing import Literal, Optional

import numpy as np
import pandas as pd
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error


app = FastAPI(
    title="BusinessMate Inventory Prediction Service",
    version="2.0.0",
)


# Random Forest baru boleh menjadi kandidat jika satu produk mempunyai
# minimal 56 hari kalender dan minimal 14 hari yang benar-benar ada penjualan.
MINIMUM_HISTORY_DAYS = 56
MINIMUM_SALES_DAYS = 14
VALIDATION_DAYS = 7
MINIMUM_RF_IMPROVEMENT = 5.0
MOVING_AVERAGE_WINDOW = 14

FEATURE_COLUMNS = [
    "time_index",
    "day_of_week",
    "is_weekend",
    "day_of_month",
    "month",
    "lag_1",
    "lag_7",
    "rolling_mean_7",
    "rolling_mean_14",
]


class SalesData(BaseModel):
    product_id: str
    date: date
    quantity: int = Field(ge=0)


class PredictionRequest(BaseModel):
    business_id: int = Field(gt=0)
    history: list[SalesData]
    prediction_days: int = Field(default=7, ge=1, le=30)


class ProductPrediction(BaseModel):
    product_id: str
    method: Literal["no_data", "moving_average", "random_forest"]
    method_label: str
    predicted_quantity: int
    last_week_quantity: int
    change_percent: Optional[float]
    data_days: int
    sales_days: int
    moving_average_mae: Optional[float]
    random_forest_mae: Optional[float]
    improvement_percent: Optional[float]
    reason: str


class PredictionResponse(BaseModel):
    business_id: int
    mode: Literal[
        "no_data",
        "moving_average",
        "random_forest",
        "hybrid",
    ]
    mode_label: str
    forecast_days: int
    percentage: Optional[float]
    predicted_total: int
    last_week_total: int
    method_counts: dict[str, int]
    products: list[ProductPrediction]
    summary: str
    generated_at: str


def weighted_moving_average(values: list[float]) -> float:
    """Rata-rata berbobot, dengan bobot lebih besar untuk data terbaru."""
    if not values:
        return 0.0

    window = values[-MOVING_AVERAGE_WINDOW:]
    weights = np.arange(1, len(window) + 1, dtype=float)

    return float(np.average(np.asarray(window, dtype=float), weights=weights))


def moving_average_forecast(
    values: list[float],
    prediction_days: int,
) -> list[float]:
    daily_prediction = max(0.0, weighted_moving_average(values))
    return [daily_prediction] * prediction_days


def prepare_product_series(rows: pd.DataFrame) -> pd.Series:
    """Gabungkan transaksi pada tanggal sama dan isi tanggal kosong dengan 0."""
    grouped = (
        rows.groupby("date", as_index=True)["quantity"]
        .sum()
        .sort_index()
        .astype(float)
    )

    full_dates = pd.date_range(
        start=grouped.index.min(),
        end=grouped.index.max(),
        freq="D",
    )

    return grouped.reindex(full_dates, fill_value=0.0)


def build_training_frame(series: pd.Series) -> pd.DataFrame:
    """Buat fitur kalender, lag, dan rolling average tanpa melihat masa depan."""
    frame = pd.DataFrame(
        {
            "date": series.index,
            "quantity": series.to_numpy(dtype=float),
        }
    )

    frame["time_index"] = np.arange(len(frame), dtype=int)
    frame["day_of_week"] = frame["date"].dt.dayofweek
    frame["is_weekend"] = frame["day_of_week"].isin([5, 6]).astype(int)
    frame["day_of_month"] = frame["date"].dt.day
    frame["month"] = frame["date"].dt.month
    frame["lag_1"] = frame["quantity"].shift(1)
    frame["lag_7"] = frame["quantity"].shift(7)
    frame["rolling_mean_7"] = (
        frame["quantity"].shift(1).rolling(window=7).mean()
    )
    frame["rolling_mean_14"] = (
        frame["quantity"].shift(1).rolling(window=14).mean()
    )

    return frame.dropna().reset_index(drop=True)


def create_random_forest() -> RandomForestRegressor:
    """Konfigurasi dibuat deterministik agar hasil pengujian dapat diulang."""
    return RandomForestRegressor(
        n_estimators=300,
        max_depth=8,
        min_samples_leaf=2,
        random_state=42,
        n_jobs=-1,
    )


def fit_random_forest(series: pd.Series) -> RandomForestRegressor:
    training_frame = build_training_frame(series)

    if training_frame.empty:
        raise ValueError("Data training Random Forest belum mencukupi.")

    model = create_random_forest()
    model.fit(
        training_frame[FEATURE_COLUMNS],
        training_frame["quantity"],
    )

    return model


def build_future_features(
    history_values: list[float],
    next_date: pd.Timestamp,
    time_index: int,
) -> pd.DataFrame:
    last_7 = history_values[-7:]
    last_14 = history_values[-14:]

    features = {
        "time_index": time_index,
        "day_of_week": next_date.dayofweek,
        "is_weekend": int(next_date.dayofweek in [5, 6]),
        "day_of_month": next_date.day,
        "month": next_date.month,
        "lag_1": history_values[-1],
        "lag_7": history_values[-7],
        "rolling_mean_7": float(np.mean(last_7)),
        "rolling_mean_14": float(np.mean(last_14)),
    }

    return pd.DataFrame([features], columns=FEATURE_COLUMNS)


def random_forest_forecast(
    model: RandomForestRegressor,
    series: pd.Series,
    prediction_days: int,
) -> list[float]:
    """Prediksi dilakukan berulang; hasil hari sebelumnya menjadi fitur lag."""
    history_values = series.to_list()
    last_date = pd.Timestamp(series.index.max())
    predictions: list[float] = []

    for day_number in range(1, prediction_days + 1):
        next_date = last_date + timedelta(days=day_number)
        feature_frame = build_future_features(
            history_values,
            next_date,
            len(history_values),
        )

        prediction = max(0.0, float(model.predict(feature_frame)[0]))
        predictions.append(prediction)
        history_values.append(prediction)

    return predictions


def calculate_improvement(
    moving_average_mae: float,
    random_forest_mae: float,
) -> float:
    if moving_average_mae <= 0:
        return 0.0

    return (
        (moving_average_mae - random_forest_mae)
        / moving_average_mae
        * 100
    )


def evaluate_models(series: pd.Series) -> dict[str, float]:
    """Backtesting: tujuh hari terakhir disembunyikan sebagai data validasi."""
    training_series = series.iloc[:-VALIDATION_DAYS]
    actual_values = series.iloc[-VALIDATION_DAYS:].to_numpy(dtype=float)

    moving_average_predictions = moving_average_forecast(
        training_series.to_list(),
        VALIDATION_DAYS,
    )

    model = fit_random_forest(training_series)
    random_forest_predictions = random_forest_forecast(
        model,
        training_series,
        VALIDATION_DAYS,
    )

    moving_average_mae = float(
        mean_absolute_error(actual_values, moving_average_predictions)
    )
    random_forest_mae = float(
        mean_absolute_error(actual_values, random_forest_predictions)
    )

    return {
        "moving_average_mae": moving_average_mae,
        "random_forest_mae": random_forest_mae,
        "improvement_percent": calculate_improvement(
            moving_average_mae,
            random_forest_mae,
        ),
    }


def calculate_change_percent(
    predicted_quantity: int,
    last_week_quantity: int,
) -> Optional[float]:
    if last_week_quantity <= 0:
        return None

    return round(
        (
            (predicted_quantity - last_week_quantity)
            / last_week_quantity
        )
        * 100,
        1,
    )


def predict_product(
    product_id: str,
    series: pd.Series,
    prediction_days: int,
) -> ProductPrediction:
    data_days = int(len(series))
    sales_days = int((series > 0).sum())
    total_sales = float(series.sum())
    last_week_quantity = int(round(float(series.iloc[-7:].sum())))

    if total_sales <= 0:
        return ProductPrediction(
            product_id=product_id,
            method="no_data",
            method_label="Belum Ada Prediksi",
            predicted_quantity=0,
            last_week_quantity=0,
            change_percent=None,
            data_days=data_days,
            sales_days=0,
            moving_average_mae=None,
            random_forest_mae=None,
            improvement_percent=None,
            reason="Produk belum memiliki riwayat penjualan.",
        )

    method: Literal["moving_average", "random_forest"] = "moving_average"
    method_label = "Moving Average"
    moving_average_mae: Optional[float] = None
    random_forest_mae: Optional[float] = None
    improvement_percent: Optional[float] = None

    eligible_for_random_forest = (
        data_days >= MINIMUM_HISTORY_DAYS
        and sales_days >= MINIMUM_SALES_DAYS
    )

    if eligible_for_random_forest:
        try:
            evaluation = evaluate_models(series)
            moving_average_mae = evaluation["moving_average_mae"]
            random_forest_mae = evaluation["random_forest_mae"]
            improvement_percent = evaluation["improvement_percent"]

            if improvement_percent >= MINIMUM_RF_IMPROVEMENT:
                method = "random_forest"
                method_label = "Random Forest"
        except (ValueError, IndexError):
            # Jika training tidak mungkin dilakukan, Moving Average tetap aman.
            method = "moving_average"
            method_label = "Moving Average"

    if method == "random_forest":
        final_model = fit_random_forest(series)
        daily_predictions = random_forest_forecast(
            final_model,
            series,
            prediction_days,
        )
        reason = (
            "Random Forest dipakai karena backtesting lebih baik "
            "daripada Moving Average."
        )
    else:
        daily_predictions = moving_average_forecast(
            series.to_list(),
            prediction_days,
        )

        if not eligible_for_random_forest:
            reason = (
                f"Data belum memenuhi minimal {MINIMUM_HISTORY_DAYS} hari "
                f"dan {MINIMUM_SALES_DAYS} hari penjualan."
            )
        elif improvement_percent is None:
            reason = "Random Forest gagal diuji, sehingga digunakan fallback."
        else:
            reason = (
                "Moving Average dipakai karena hasil backtesting Random Forest "
                "belum lebih baik minimal "
                f"{MINIMUM_RF_IMPROVEMENT:.0f}%."
            )

    predicted_quantity = int(np.ceil(sum(daily_predictions)))

    return ProductPrediction(
        product_id=product_id,
        method=method,
        method_label=method_label,
        predicted_quantity=predicted_quantity,
        last_week_quantity=last_week_quantity,
        change_percent=calculate_change_percent(
            predicted_quantity,
            last_week_quantity,
        ),
        data_days=data_days,
        sales_days=sales_days,
        moving_average_mae=(
            round(moving_average_mae, 3)
            if moving_average_mae is not None
            else None
        ),
        random_forest_mae=(
            round(random_forest_mae, 3)
            if random_forest_mae is not None
            else None
        ),
        improvement_percent=(
            round(improvement_percent, 1)
            if improvement_percent is not None
            else None
        ),
        reason=reason,
    )


def determine_mode(products: list[ProductPrediction]) -> str:
    active_methods = {
        product.method
        for product in products
        if product.method != "no_data"
    }

    if not active_methods:
        return "no_data"

    if len(active_methods) == 1:
        return active_methods.pop()

    return "hybrid"


def mode_label(mode: str) -> str:
    labels = {
        "no_data": "Belum Ada Prediksi",
        "moving_average": "Moving Average",
        "random_forest": "Random Forest",
        "hybrid": "Hybrid",
    }
    return labels.get(mode, "Belum Ada Prediksi")


@app.post("/predict", response_model=PredictionResponse)
async def predict(request: PredictionRequest) -> PredictionResponse:
    try:
        if not request.history:
            return PredictionResponse(
                business_id=request.business_id,
                mode="no_data",
                mode_label="Belum Ada Prediksi",
                forecast_days=request.prediction_days,
                percentage=None,
                predicted_total=0,
                last_week_total=0,
                method_counts={
                    "no_data": 0,
                    "moving_average": 0,
                    "random_forest": 0,
                },
                products=[],
                summary="Belum ada data penjualan untuk diprediksi.",
                generated_at=datetime.now(timezone.utc).isoformat(),
            )

        history_frame = pd.DataFrame(
            [row.model_dump() for row in request.history]
        )
        history_frame["date"] = pd.to_datetime(history_frame["date"])

        products: list[ProductPrediction] = []

        for product_id, product_rows in history_frame.groupby(
            "product_id",
            sort=True,
        ):
            series = prepare_product_series(product_rows)
            products.append(
                predict_product(
                    str(product_id),
                    series,
                    request.prediction_days,
                )
            )

        mode = determine_mode(products)
        predicted_total = sum(
            product.predicted_quantity for product in products
        )
        last_week_total = sum(
            product.last_week_quantity for product in products
        )
        percentage = calculate_change_percent(
            predicted_total,
            last_week_total,
        )
        method_counts = dict(
            Counter(product.method for product in products)
        )

        return PredictionResponse(
            business_id=request.business_id,
            mode=mode,
            mode_label=mode_label(mode),
            forecast_days=request.prediction_days,
            percentage=percentage,
            predicted_total=predicted_total,
            last_week_total=last_week_total,
            method_counts=method_counts,
            products=products,
            summary=(
                f"Prediksi {predicted_total} unit untuk "
                f"{request.prediction_days} hari menggunakan "
                f"mode {mode_label(mode)}."
            ),
            generated_at=datetime.now(timezone.utc).isoformat(),
        )
    except Exception as error:
        raise HTTPException(status_code=500, detail=str(error)) from error


@app.get("/health")
async def health() -> dict[str, object]:
    return {
        "status": "ok",
        "minimum_history_days": MINIMUM_HISTORY_DAYS,
        "minimum_sales_days": MINIMUM_SALES_DAYS,
        "minimum_rf_improvement_percent": MINIMUM_RF_IMPROVEMENT,
    }


if __name__ == "__main__":
    import uvicorn

    # Diikat ke localhost agar service prediksi tidak terbuka ke jaringan luar.
    uvicorn.run(app, host="127.0.0.1", port=8001)