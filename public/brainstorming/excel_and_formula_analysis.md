# 📊 LAPORAN LENGKAP STRUKTUR EXCEL & ANALISIS FORMULA (2024 - 2026)
**Sumber Data**: Direktori `D:\2024 PTP`, `D:\2025 PTP`, `D:\2026 PTP` (Total 91 File Laporan Kinerja Peralatan Pelabuhan).  
**Lokasi File**: `C:\laragon\www\asset-analytics\public\brainstorming\excel_and_formula_analysis.md`

---

## 1. Arsitektur Sheet pada Template Laporan Excel PTP

Setiap file Excel laporan bulanan memiliki struktur sheet baku:

| Nama Sheet | Fungsi Utama | Keterkaitan (Relasi) Data |
| :--- | :--- | :--- |
| **`PERFORMANCE`** | Dashboard rekapitulasi performa bulanan seluruh alat | Menghitung Availability, MTBF, MTTRc, MTTRp, Utilisasi dari sheet lain |
| **`DATA BD`** | Log detail kerusakan, waktu mulai & selesai breakdown | Dihitung durasinya `= 24 * (Finish - Start)` dan dirujuk ke kolom Total Breakdown |
| **`ACCIDENT`** | Log insiden/kecelakaan alat (Boom bengkok, tabrakan) | Dijumlahkan ke kolom Downtime Accident di sheet `PERFORMANCE` via `SUMIF` |
| **`BBM`** | Rekap konsumsi bahan bakar (Liter & Biaya) | Dirujuk ke kolom BBM & Rasio Liter/Jam di sheet `PERFORMANCE` |
| **`PART GROUP`** | Master data klasifikasi komponen rusak | Acuan dropdown di sheet `DATA BD` (Engine, Drive, Spreader, Boom, dll) |
| **`KERUSAKAN`** | Master deskripsi gejala kerusakan | Acuan dropdown jenis kerusakan di `DATA BD` |
| **`PENYEBAB`** | Master akar masalah (Short Circuit, Lifetime, Operator) | Acuan dropdown penyebab kerusakan di `DATA BD` |
| **`TINDAKAN`** | Master tindakan perbaikan (Ganti Part, Kalibrasi) | Catatan solusi maintenance |

---

## 2. Pemetaan Formula Kolom pada Sheet `PERFORMANCE`

Pada baris data alat (dimulai dari Baris 12), kolom Excel dihitung menggunakan relasi matematis berikut:

```text
Kolom A  : Nomor Urut
Kolom B  : Group Alat (QCC, RTG, RS, FLT, TTR, SIL)
Kolom C  : Nomor Alat (Contoh: QCC-0001, RTG-0010)
Kolom D  : Deskripsi / Spesifikasi Alat
Kolom E  : PM (Preventive Maintenance Duration dalam Jam)
Kolom F  : CM (Corrective Maintenance Duration dalam Jam)
Kolom G  : Accident Duration = SUMIF(ACCIDENT!$B$8:$B$1996, C12, ACCIDENT!$E$8:$E$1996)
Kolom H  : Breakdown Duration (Jam)
Kolom I  : Total Breakdown Duration (Jam)
Kolom J  : Number of Breakdown (Frekuensi kerusakan dalam 1 bulan)
Kolom K  : Operation Time (Running Hour Meter / HRM dalam Jam)
Kolom L  : Available Time = 24 * Jumlah Hari (672 / 696 / 720 / 744 Jam)
Kolom M  : Total Non-Available Time = E + F + G + I
Kolom N  : Availability = (L - M) / L
Kolom O  : MTBF = IFERROR((K / J), "NO BD")
Kolom P  : MTTRc = IFERROR((H / J), "NO BD")
Kolom Q  : MTTRp = IFERROR((I / J), "NO BD")
Kolom R  : Utilisasi = K / L
Kolom S  : Total Konsumsi BBM (Liter)
Kolom T  : Total Konsumsi Listrik (kWh)
Kolom U  : Produksi (Jumlah Box Petikemas)
Kolom V  : Konsumsi BBM per Jam = S / K
Kolom W  : Konsumsi Listrik per Jam = T / K
Kolom X  : Konsumsi BBM per Box = S / U
Kolom Y  : Konsumsi Listrik per Box = T / U
```

---

## 3. Evaluasi & Perbandingan dengan Formula Sistem Saat Ini

### A. Hubungan Parameter terhadap Kesehatan Alat (Health Score)

| Parameter | Pengaruh Terhadap Kesehatan | Interpretasi Bisnis |
| :--- | :--- | :--- |
| **Availability ($N$)** | **Sangat Tinggi (Positif)** | Kesiapan alat melayani kapal & lapangan. Turun drastis jika ada PM panjang, breakdown, atau accident. |
| **MTBF ($O$)** | **Tinggi (Positif)** | *Mean Time Between Failure* — semakin lama alat bekerja tanpa rusak, semakin handal. |
| **MTTRp ($Q$)** | **Tinggi (Negatif)** | *Mean Time to Repair* — semakin cepat diperbaiki saat breakdown, semakin baik kapabilitas tim maintenance. |
| **Utilisasi ($R$)** | **Sedang (Positif/Konteks)** | Rasio pemakaian alat. Alat standby / backup utilisasinya rendah meski fisiknya prima. |
| **Accident ($G$)** | **Penalti Berat** | Kerusakan akibat human error / benturan luar yang menonaktifkan alat secara tiba-tiba. |

### B. Rumus Health Score Rekomendasi (Ponytail Lean Standard):

$$S_{MTBF} = \begin{cases} 100 & \text{jika } J = 0 \text{ (Tidak ada Breakdown / NO BD)} \\ \min\left(\frac{MTBF}{MTBF_{max}} \times 100, 100\right) & \text{jika } J > 0 \end{cases}$$

$$S_{MTTRp} = \begin{cases} 100 & \text{jika } J = 0 \text{ (Tidak ada Breakdown / NO BD)} \\ \max\left(\left(1 - \frac{MTTRp}{MTTRp_{max}}\right) \times 100, 0\right) & \text{jika } J > 0 \end{cases}$$

$$\text{Health Score} = (0.40 \times N) + (0.30 \times S_{MTBF}) + (0.20 \times S_{MTTRp}) + (0.10 \times R)$$

$$\text{Priority Score} = 100 - \text{Health Score}$$

---

## 4. Kesimpulan & Status Penyimpanan
- Seluruh 91 file Excel (2024, 2025, 2026) telah diverifikasi memiliki struktur formula yang seragam.
- Laporan analisis formula ini telah tersimpan permanen di:  
  `C:\laragon\www\asset-analytics\public\brainstorming\excel_and_formula_analysis.md`.
