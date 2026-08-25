# 📊 DOKUMEN BRAINSTORMING & ANALISIS HEALTH SCORE PERALATAN PELABUHAN
**Sumber Data**: Dokumen Standarisasi Kinerja Peralatan Pelabuhan PT Pelindo Terminal Petikemas & 90+ File Laporan Bulanan (2024, 2025, 2026).  
**Lokasi Output**: `C:\laragon\www\asset-analytics\public\brainstorming\health_score_analysis.md`

---

## 1. Pemahaman Parameter & Formula Standar (Pelindo PTP)

Berdasarkan dokumen resmi *STANDARISASI LAPORAN KINERJA PERALATAN 2023*:

### A. Waktu Dasar & Kategori Waktu
1. **Available Time ($L$)**: Total jam dalam sebulan ($24 \text{ jam} \times \text{hari dalam bulan} = 672 / 696 / 720 / 744 \text{ jam}$).
2. **Operation Time ($K$)**: Jam alat beroperasi berdasarkan Running Hour Meter (HRM).
3. **Non-Available Time / Downtime ($M$)**:
   $$M = E + F + G + I$$
   - **$E$ (Preventive Maintenance / PM)**: Perawatan terencana (Regular service HM 250/500/1000, overhaul, greasing). *Catatan: Inspeksi dan painting tidak mengurangi availability.*
   - **$F$ (Corrective Maintenance / CM)**: Perbaikan terencana saat alat tidak operasi (penggantian ban tipis saat inspeksi, tambah air aki, ganti wirerope rantas).
   - **$G$ (Accident)**: Kerusakan akibat benturan, insiden operasional, atau kecelakaan kerja.
   - **$H$ (Breakdown Time)**: Durasi sejak alat breakdown hingga perbaikan selesai / digantikan backup / operasi kapal selesai. Digunakan untuk menghitung $MTTR_c$.
   - **$I$ (Total Breakdown Time)**: Durasi total sejak alat breakdown hingga alat benar-benar siap beroperasi kembali ($Ready$). Digunakan untuk menghitung $MTTR_p$.
   - **$J$ (Number of Breakdown)**: Frekuensi kejadian breakdown dalam satu bulan pelaporan.

### B. KPI Utama Peralatan
1. **Availability ($N$)**:
   $$Availability = \frac{L - M}{L} = \frac{\text{Available Time} - \text{Total Downtime}}{\text{Available Time}}$$
2. **Utilisation ($R$)**:
   $$Utilisation = \frac{K}{L} = \frac{\text{Operation Time (HRM)}}{\text{Available Time}}$$
3. **MTBF (Mean Time Between Failure) ($O$)**:
   $$MTBF = \frac{K}{J} = \frac{\text{Operation Time}}{\text{Number of Breakdown}}$$
   *Jika $J = 0$ (tidak ada breakdown), standar PTP mencatat `NO BD` (bukan 0 atau error).*
4. **MTTRc (Mean Time to Recover) ($P$)**:
   $$MTTR_c = \frac{H}{J} = \frac{\text{Breakdown Time}}{\text{Number of Breakdown}}$$
5. **MTTRp (Mean Time to Repair) ($Q$)**:
   $$MTTR_p = \frac{I}{J} = \frac{\text{Total Breakdown Time}}{\text{Number of Breakdown}}$$

---

## 2. Evaluasi Formula Health Score Saat Ini di Sistem

Formula yang saat ini aktif di `AnalysisService.php`:

```php
// 1. Normalisasi
Availability (%) = availability * 100
Utilisation  (%) = utilisation * 100
MTBF Score   (%) = min((MTBF / MTBF_MAX) * 100, 100)
MTTRp Score  (%) = max((1 - (MTTRp / MTTRp_MAX)) * 100, 0)

// 2. Pembobotan Health Score
Health Score = (0.35 * Availability) + (0.25 * Utilisation) + (0.25 * MTBF Score) + (0.15 * MTTRp Score)
Priority     = 100 - Health Score
```

### Temuan Kritis (Critical Insights & Gotchas):
1. **Kasus `NO BD` ($J = 0$)**:
   - Jika alat beroperasi tanpa ada breakdown sama sekali, $MTBF$ di Excel bernilai `NO BD` (null/0 di database).
   - Di formula saat ini, jika $MTBF = 0$, maka $MTBF\_Score = 0$, padahal alat tersebut **sangat sehat** (karena tidak pernah rusak).
   - **Rekomendasi Ponytail**: Jika $J = 0$ dan alat beroperasi ($K > 0$), $MTBF\_Score$ harus bernilai **100** (skor sempurna), bukan 0!
2. **Pengaruh Accident ($G$)**:
   - Accident tidak masuk ke $J$ (Number of Breakdown), namun masuk ke $M$ (Non-Available Time) yang menjatuhkan Availability hingga 0% (seperti kasus QCC-0001 pada Jan 2026). Formula Availability saat ini sudah menangkap penalti ini secara akurat.
3. **Skala Utilisation ($R$)**:
   - Utilisasi di pelabuhan sangat bervariasi antar jenis alat (QCC/RTG sekitar 30-60%, alat backup/forklift mungkin <20%). Menjadikan Utilisasi berbobot tinggi (25%) dapat membiaskan skor alat backup yang kondisinya prima tetapi jarang dipanggil operasi.

---

## 3. Opsi Brainstorming Model Health Score

### Opsi 1: Enhanced Weighted Standard (Rekomendasi - Ponytail Mode)
Mempertahankan formula linier yang simpel, cepat, dan transparan, namun memperbaiki logika edge case $J = 0$:
- **Bobot**:
  - Availability ($40\%$): Mengukur kesiapan fisik alat (termasuk penalti accident & breakdown).
  - Reliability / MTBF ($30\%$): Mengukur ketahanan alat. Jika $J = 0$, skor $100$.
  - Maintainability / MTTRp ($20\%$): Mengukur kecepatan pemulihan saat rusak. Jika $J = 0$, skor $100$.
  - Utilisation ($10\%$): Porsi kecil agar tidak mendiskriminasi alat cadangan/standby.
- **Formula**:
  $$\text{Health Score} = 0.40 \times N + 0.30 \times S_{MTBF} + 0.20 \times S_{MTTRp} + 0.10 \times R$$

### Opsi 2: Category-Specific Benchmark Matrix
Menerapkan batasan $MTBF_{max}$ dan target utilisasi per kategori alat (QCC, RTG, RS, FLT, HDT), karena karakteristik beban kerja RTG berbeda drastis dengan Forklift.

---

## 4. Ringkasan Aksi & Keputusan
1. File brainstorming lengkap telah tersimpan di direktori `public/brainstorming/health_score_analysis.md`.
2. Menghindari over-engineering: tidak perlu library machine learning berat jika standardisasi formula matematis di `AnalysisService.php` sudah memetakan bobot industri Pelindo dengan benar.
