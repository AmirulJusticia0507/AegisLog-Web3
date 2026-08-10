**Fungsi:** Roadmap pengerjaan bertahap dan target performa (KPI) sistem.

# Tujuan Proyek & Peta Jalan Rekayasa (AegisLog Web3 - Q3/Q4 2026)

---

## 🎯 Sasaran Strategis

1. **Jaminan Integritas 100%**: Memastikan berkas audit yang dijangkarkan ke *smart contract* memiliki ketahanan mutlak terhadap manipulasi *database off-chain*.
2. **Performa Pencocokan Cepat**: Sistem *cron worker* sanggup memverifikasi 10.000 log audit per menit menggunakan kueri PostgreSQL dan *batch RPC calls*.
3. **Pengalaman Pengguna Tanpa Hambatan**: Proses penjangkaran berkas hingga mendapat kepastian status *on-chain* selesai dalam waktu kurang dari 5 detik.

---

## 🗺 Peta Jalan Rekayasa Q3 - Q4 2026

### Fase 1: Fondasi & Smart Contract (September 2026)

- [X] Inisialisasi proyek Laravel 13 (PHP 8.4) dan Vue 3 Composition API.
- [X] Pembuatan dan uji coba *Smart Contract* `AuditVault.sol` di jaringan lokal Anvil/Hardhat.
- [X] Implementasi otentikasi SIWE (Sign-In with Ethereum) di backend dan frontend.

### Fase 2: Pipeline Penjangkaran & Real-Time Alerting (Oktober 2026)

- [ ] Pengintegrasian perhitungan *hash client-side* di Vue 3 dengan Viem.
- [ ] Pembuatan Action Class Laravel 13 untuk enkripsi berkas AES-256 dan penyimpanannya ke PostgreSQL.
- [ ] Konfigurasi **Laravel Reverb** untuk dorongan notifikasi *tampering* secara *real-time*.

### Fase 3: Worker Audit Otomatis & Dashboard Cyberpunk (November 2026)

- [ ] Pembangunan *scheduled task* Laravel untuk verifikasi berkala antara PostgreSQL dan *Smart Contract*.
- [ ] Penyempurnaan UI *Cyber-Security Dashboard* Vue 3 dengan tabel reaktif dan grafik statistik.

---

## 📊 Indikator Kinerja Utama (KPIs)

| Metrik                              | Target Sasaran               | Alat Ukur                    |
| :---------------------------------- | :--------------------------- | :--------------------------- |
| **Kecepatan Respons API**           | `< 75ms` (P95)               | Laravel Pulse                |
| **Waktu Deteksi Tampering**         | `< 1.0 detik` dari insiden   | Event Log Laravel Reverb     |
| **Tingkat Akurasi Hash Sync**       | `100%`                       | Integration Test & Audit Log |
