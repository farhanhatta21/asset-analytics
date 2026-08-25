from pathlib import Path

import joblib
import pandas as pd

BASE_DIR = Path(__file__).resolve().parent

# memuat model machine learning
model = joblib.load(
    BASE_DIR / "asset_health_model.pkl"
)

# memuat dataset machine learning
df = pd.read_csv(
    BASE_DIR / "prediction_dataset.csv"
)

latest_data = df.copy()

print()

print("=" * 45)
print("LATEST DATA UNTUK PREDIKSI")
print("=" * 45)
print(
    latest_data[
        ["nama_alat", "periode"]
    ].head(30)
)

print()

print(
    "Periode Historis :",
    latest_data["periode"].min(),
    "-",
    latest_data["periode"].max()
)

# fitur yang dihunakan model
feature_columns = [

    "avail_t1",
    "avail_t2",
    "avail_t3",

    "mtbf_t1",
    "mtbf_t2",
    "mtbf_t3",

    "mttrp_t1",
    "mttrp_t2",
    "mttrp_t3",

    "num_bd_t1",
    "num_bd_t2",
    "num_bd_t3",

    "breakdown_frequency_t1",
    "breakdown_frequency_t2",
    "breakdown_frequency_t3"

]

# menyiapkan data input untuk proses prediksi
X_latest = latest_data[
    feature_columns
]



print()

print("=" * 45)
print("VALIDASI DATASET PREDIKSI")
print("=" * 45)

print(f"Jumlah Asset     : {len(latest_data)}")
print(f"Jumlah Fitur     : {len(feature_columns)}")
print(
    f"Missing Value    : {latest_data[feature_columns].isna().sum().sum()}"
)

# menghasilkan prediksi
predicted_scores = model.predict(
    X_latest
)

# membuat tabel hasil prediksi
prediction = latest_data[
    [
        "nama_alat",
        "periode"
    ]
].copy()

# menyimpan periode historis terakhir
prediction.rename(
    columns={
        "periode": "last_period"
    },
    inplace=True
)


# menentukan periode target prediksi
next_period = (
    pd.to_datetime(prediction["last_period"])
    + pd.DateOffset(months=1)
)

prediction["prediction_period"] = next_period.dt.strftime("%Y-%m")


# hasil prediksi health score
prediction[
    "predicted_health_score"
] = predicted_scores.round(2)

# maintenance risk score
prediction[
    "maintenance_risk_score"
] = (
    100
    -
    prediction[
        "predicted_health_score"
    ]
).round(2)

# simpan hasil prediksi
prediction.to_csv(

    BASE_DIR / "prediction_results.csv",

    index=False

)

print()

print("Prediction berhasil dibuat")
print()

print()

print(
    f"Output File : {BASE_DIR / 'prediction_results.csv'}"
)


print()

print("=" * 45)

print("RINGKASAN HASIL PREDIKSI")

print("=" * 45)

print(
    f"Jumlah Asset              : {len(prediction)}"
)

print(
    f"Periode Prediksi          : {prediction['prediction_period'].iloc[0]}"
)

print(
    f"Rata-rata Health Score    : {prediction['predicted_health_score'].mean():.2f}"
)

print(
    f"Rata-rata Risk Score      : {prediction['maintenance_risk_score'].mean():.2f}"
)

print(
    f"Health Score Minimum      : {prediction['predicted_health_score'].min():.2f}"
)

print(
    f"Health Score Maksimum     : {prediction['predicted_health_score'].max():.2f}"
)


print()

print()

print("=" * 45)
print("CONTOH HASIL PREDIKSI")
print("=" * 45)

print(
    prediction[
        [
            "nama_alat",
            "last_period",
            "prediction_period",
            "predicted_health_score",
            "maintenance_risk_score"
        ]
    ]
    .head(10)
    .to_string(index=False)
)