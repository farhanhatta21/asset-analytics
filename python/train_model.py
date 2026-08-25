from pathlib import Path

import pandas as pd
import joblib
import matplotlib.pyplot as plt

from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import (
    mean_absolute_error,
    mean_squared_error,
    r2_score
)


BASE_DIR = Path(__file__).resolve().parent

df = pd.read_csv(
    BASE_DIR / "ml_training_dataset.csv"
)

print(
    df["periode"]
    .value_counts()
    .sort_index()
)

# FITUR
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


# SPLIT DATA BERDASARKAN URUTAN WAKTU
df = df.sort_values(
    ["periode", "nama_alat"]
).reset_index(drop=True)


# SPLIT BERDASARKAN PERIODE
periods = sorted(df["periode"].unique())

split_period = int(len(periods) * 0.8)

train_periods = periods[:split_period]
test_periods = periods[split_period:]

train_df = df[
    df["periode"].isin(train_periods)
].copy()

test_df = df[
    df["periode"].isin(test_periods)
].copy()

X_train = train_df[feature_columns]
y_train = train_df["target_health_score"]

X_test = test_df[feature_columns]
y_test = test_df["target_health_score"]

print()

print("=" * 45)
print("      TRAINING MACHINE LEARNING")
print("=" * 45)

print(f"Jumlah Asset            : {df['nama_alat'].nunique()}")
print(f"Jumlah Dataset          : {len(df)}")

print()

print("Window Size             : 3 Periode")
print("Target                  : Health Score Periode Berikutnya")

print()

print("Rentang Dataset")
print("-" * 45)

print(f"Periode Awal            : {df['periode'].min()}")
print(f"Periode Akhir           : {df['periode'].max()}")

print()

print("Split Dataset")
print("-" * 45)

print(f"Training Sample         : {len(train_df)}")
print(f"Testing Sample          : {len(test_df)}")

print()

print("Periode Training")
print(
    " s/d ".join([
        train_periods[0],
        train_periods[-1]
    ])
)

print()

print("Periode Testing")
print(test_periods)

# MODEL
model = RandomForestRegressor(
    n_estimators=200,
    max_depth=10,
    random_state=42
)

model.fit(
    X_train,
    y_train
)

importance = pd.DataFrame({

    "Feature": feature_columns,

    "Importance": model.feature_importances_

}).sort_values(

    by="Importance",

    ascending=False

)

print()

print("=" * 45)
print("FEATURE IMPORTANCE")
print("=" * 45)

print(
    importance.to_string(index=False)
)

print()

print("=" * 45)
print("Model berhasil dilatih")
print("=" * 45)

print()
print("Training :", len(X_train))
print("Testing  :", len(X_test))

# EVALUASI
prediction = model.predict(X_test)

evaluation = pd.DataFrame({
    "Actual": y_test.values,
    "Prediction": prediction.round(2)
})

evaluation.to_csv(
    BASE_DIR / "evaluation_results.csv",
    index=False
)

print()

print()

print("=" * 45)
print("CONTOH HASIL PREDIKSI")
print("=" * 45)

print(evaluation.head(10).to_string(index=False))

# MAE
mae = mean_absolute_error(y_test, prediction)

#RMSE
rmse = mean_squared_error(
    y_test,
    prediction
) ** 0.5

#R2
r2 = r2_score(y_test, prediction)

#Pearson
pearson = (
    pd.Series(y_test.values)
    .corr(
        pd.Series(prediction),
        method="pearson"
    )
)
print()

print()

print("=" * 45)
print("HASIL EVALUASI MODEL")
print("=" * 45)

print(f"MAE                 : {mae:.3f}")
print(f"RMSE                : {rmse:.3f}")
print(f"R² Score            : {r2:.3f}")
print(f"Pearson Correlation : {pearson:.3f}")

print()
print("INTERPRETASI")
print("-" * 45)
print(
    f"MAE sebesar {mae:.2f} menunjukkan rata-rata kesalahan prediksi sebesar {mae:.2f} poin Health Score."
)
print(
    f"RMSE sebesar {rmse:.2f} menunjukkan besar kesalahan prediksi dengan memberikan penalti lebih besar terhadap error yang tinggi."
)
print(
    f"R² sebesar {r2:.3f} menunjukkan proporsi variasi Health Score yang dapat dijelaskan oleh model."
)
print(
    f"Pearson Correlation sebesar {pearson:.3f} menunjukkan kekuatan hubungan linear antara nilai aktual dan hasil prediksi."
)


# VISUALISASI HASIL PREDIKSI (scatter plot)
plt.figure(figsize=(6,6))

plt.scatter(
    y_test,
    prediction,
    s=40
)

minimum = min(
    y_test.min(),
    prediction.min()
)

maximum = max(
    y_test.max(),
    prediction.max()
)

plt.plot(
    [minimum, maximum],
    [minimum, maximum],
    linestyle="--"
)

plt.xlabel("Actual Health Score")

plt.ylabel("Predicted Health Score")

plt.title("Actual vs Prediction")

plt.grid(True)

plt.savefig(
    BASE_DIR / "prediction_scatter.png",
    dpi=300,
    bbox_inches="tight"
)

plt.close()

print()
print("Scatter Plot berhasil disimpan.")

# SIMPAN MODEL
joblib.dump(
    model,
    BASE_DIR / "asset_health_model.pkl"
)

print()

print("Model berhasil disimpan.")