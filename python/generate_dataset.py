from pathlib import Path

import pandas as pd
import pymysql

from config import DB_CONFIG

# Folder python/
BASE_DIR = Path(__file__).resolve().parent

conn = pymysql.connect(**DB_CONFIG)

query = """
SELECT

    a.nama_alat,
    a.periode,

    a.availability,
    a.utilisation,
    a.mtbf,
    a.mttrc,
    a.mttrp,

    a.accident,
    a.available_time,
    a.breakdown_duration,
    a.total_breakdown,
    a.number_of_breakdowns,

    COUNT(b.id) AS breakdown_frequency

FROM assets a

LEFT JOIN breakdown_logs b

ON
    a.nama_alat = b.nama_alat
AND
    a.periode = b.periode

GROUP BY

    a.nama_alat,
    a.periode,
    a.availability,
    a.utilisation,
    a.mtbf,
    a.mttrc,
    a.mttrp,
    a.accident,
    a.available_time,
    a.breakdown_duration,
    a.total_breakdown,
    a.number_of_breakdowns

ORDER BY

    a.nama_alat,
    a.periode
"""

df = pd.read_sql(query, conn)

conn.close()

print("\n=== DATA FLT-0001 ===")

print(
    df[
        df["nama_alat"] == "FLT-0001"
    ][
        [
            "nama_alat",
            "periode",
            "availability",
            "utilisation"
        ]
    ]
)

print("\n=== DATA PERIODE 2025-12 ===")

print(
    df[
        df["periode"] == "2025-12"
    ][
        [
            "nama_alat",
            "periode"
        ]
    ]
)

# 
print(df.head())

print()

print("Jumlah record :", len(df))

print("Jumlah alat :", df["nama_alat"].nunique())

# urutkan data
df = df.sort_values(
    ["nama_alat", "periode"]
)

# lihat data salah satu alat
print()

print(
    df[
        df["nama_alat"] == "FLT-0001"
    ]
)

print()

# parameter yg digunakan sebagai fitur lag
# utilisation dibuang (bukan komponen health score)
# number_of_breakdowns ditambahkan (J = variabel kunci formula MTBF baru)
feature_map = {
    "availability": "avail",
    "mtbf": "mtbf",
    "mttrp": "mttrp",
    "number_of_breakdowns": "num_bd",
    "breakdown_frequency": "breakdown_frequency"
}

dataset = df.copy()

# pastikan semua data numerik
for column in feature_map.keys():
    dataset[column] = pd.to_numeric(
        dataset[column],
        errors="coerce"
    )

# NILAI KOSONG PADA METRIK NON-UTAMA DIANGGAP 0
dataset["accident"] = dataset["accident"].fillna(0)
dataset["available_time"] = dataset["available_time"].fillna(0)
dataset["breakdown_frequency"] = dataset[
    "breakdown_frequency"
].fillna(0)

# Pastikan data sudah terurut kronologis
dataset = dataset.sort_values(
    ["nama_alat", "periode"]
).reset_index(drop=True)

for column, prefix in feature_map.items():

    dataset[f"{prefix}_t1"] = (
        dataset
        .groupby("nama_alat")[column]
        .shift(2)
    )

    dataset[f"{prefix}_t2"] = (
        dataset
        .groupby("nama_alat")[column]
        .shift(1)
    )

    dataset[f"{prefix}_t3"] = (
        dataset[column]
    )

print()

print(
    dataset.head(10)
)

print()
print("Dataset setelah feature engineering")
print(dataset.head())

# HITUNG TARGET HEALTH SCORE (formula baru: 60-30-10, tanpa utilisasi)
# Mengikuti formula yang sama dengan AnalysisService.php di Laravel

# S_Avail: target absolut 85% — avail >= 85% dapat skor 100
availability_pct = dataset["availability"].apply(
    lambda x: x * 100 if x <= 1 else x
)
dataset["availability_score"] = (availability_pct / 85 * 100).clip(upper=100, lower=0)

# S_MTBF: berbasis frekuensi breakdown (number_of_breakdowns)
# J=0 (NO BD) -> 100 (sempurna). J>0 -> min(100/J, 100)
dataset["mtbf_score"] = dataset["number_of_breakdowns"].apply(
    lambda j: 100.0 if j <= 0 else min(100.0 / j, 100.0)
)

# S_MTTRp: durasi perbaikan vs total jam bulan (available_time)
# J=0 (NO BD) -> 100. J>0 -> max((1 - MTTRp/L) * 100, 0)
default_L = 720  # default jika available_time kosong
dataset["mttrp_score"] = dataset.apply(
    lambda row: 100.0
    if row["number_of_breakdowns"] <= 0
    else max((1 - (row["mttrp"] / (row["available_time"] if row["available_time"] > 0 else default_L))) * 100, 0),
    axis=1
)

# BASE HEALTH SCORE: 60% Avail + 30% MTBF + 10% MTTRp
dataset["base_health_score"] = (
    0.60 * dataset["availability_score"]
    + 0.30 * dataset["mtbf_score"]
    + 0.10 * dataset["mttrp_score"]
).clip(0, 100)


# DATASET UNTUK TRAINING dengan sliding windoe
training_rows = []

for nama_alat, group in dataset.groupby("nama_alat"):

    group = group.sort_values("periode").reset_index(drop=True)

    if len(group) < 4:
        continue

    for i in range(3, len(group)):

        row = group.iloc[i - 1].copy()

        row["target_health_score"] = group.iloc[i]["base_health_score"]

        training_rows.append(row)

training_dataset = pd.DataFrame(training_rows)

print()
print("Jumlah sampel training :", len(training_dataset))
print()
print(
    training_dataset[
        [
            "nama_alat",
            "periode",
            "target_health_score"
        ]
    ].head(20)
)

required_columns = [
    "availability",
    "utilisation",
    "mtbf",
    "mttrp"
]

history_columns = []

for prefix in feature_map.values():
    history_columns.extend([
        f"{prefix}_t1",
        f"{prefix}_t2"
    ])

training_dataset = training_dataset.dropna(
    subset=
        required_columns
        + history_columns
        + ["target_health_score"]
).reset_index(drop=True)

training_dataset["target_health_score"] = (
    training_dataset["target_health_score"]
    .clip(0, 100)
    .round(2)
)

print()
print("Training Dataset")
print(
    training_dataset[
        [
            "nama_alat",
            "periode",
            "target_health_score"
        ]
    ].head()
)

# DATASET PREDICTION
prediction_dataset = (
    dataset
    .groupby("nama_alat")
    .tail(1)
    .reset_index(drop=True)
)

required_columns = [
    "availability",
    "utilisation",
    "mtbf",
    "mttrp"
]

history_columns = []

for prefix in feature_map.values():
    history_columns.extend([
        f"{prefix}_t1",
        f"{prefix}_t2"
    ])

prediction_dataset = prediction_dataset.dropna(
    subset=required_columns + history_columns
).reset_index(drop=True)


training_dataset.to_csv(
    BASE_DIR / "ml_training_dataset.csv",
    index=False
)

prediction_dataset.to_csv(
    BASE_DIR / "prediction_dataset.csv",
    index=False
)

print()
print("========== HASIL GENERATE DATASET ==========")
print()

print("Jumlah Training Dataset :", len(training_dataset))
print("Jumlah Prediction Dataset :", len(prediction_dataset))

print()

print("Contoh Training Dataset")
print(
    training_dataset[
        [
            "nama_alat",
            "periode",
            "target_health_score"
        ]
    ].head(10)
)

print()

print("Contoh Prediction Dataset")
print(
    prediction_dataset[
        [
            "nama_alat",
            "periode"
        ]
    ].head(10)
)

print()

print("Dataset berhasil dibuat.")
