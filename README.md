**Fungsi:** Halaman utama repositori yang menjelaskan tujuan proyek,  *tech stack* , dan cara menjalankan aplikasi.


# AegisLog Web3 — Platform Audit Keamanan & Log Forensik Digital Anti-Manipulasi

**AegisLog Web3** adalah platform enterprise hybrid (Web2 + Web3) yang dirancang untuk menjamin integritas berkas audit keamanan, laporan pengujian penetrasi (*penetration test*), dan *log* forensik digital.

Dengan menggabungkan kekuatan **Laravel 13 (PHP 8.4)**, **Vue 3 (Composition API)**, **PostgreSQL 16+**, dan **Smart Contract EVM (Viem / Wagmi v2)**, AegisLog mengunci *hash cryptographic* (SHA-256) dari setiap berkas laporan secara permanen ke jaringan blockchain. Jika ada pihak yang mencoba mengubah atau menghapus data *off-chain* di basis data internal, sistem akan mendeteksi pembongkaran (*tampering*) secara *real-time* melalui pembandingan otomatis dengan data *on-chain*.

---

## 🚀 Fitur Utama

- **Otentikasi SIWE (Sign-In with Ethereum)**: *Login* aman tanpa kata sandi konvensional menggunakan tanda tangan kriptografi *wallet* Web3 (MetaMask, WalletConnect, Coinbase Wallet).
- **Penjangkaran Hash On-Chain (Proof-of-Audit)**: Menghasilkan *hash* SHA-256 berkas di tingkat klien (*browser-side*) dan menyimpannya ke dalam *Smart Contract* (`AuditVault.sol`).
- **Pencocokan Integritas Otomatis**: *Worker queue* Laravel 13 secara berkala membandingkan *hash* berkas di PostgreSQL dengan *hash* *on-chain*.
- **Deteksi Tampering Real-Time**: Notifikasi bawaan **Laravel Reverb** mendorong peringatan *WebSocket* langsung ke *dashboard* Vue 3 jika terjadi ketidakcocokan *hash*.
- **Penyimpanan Terenkripsi Off-Chain**: Berkas laporan asli dienkripsi menggunakan algoritma AES-256-GCM sebelum disimpan ke penyimpanan lokal/S3, menjaga kerahasiaan data sesuai aturan UU PDP.

---

## 🛠 Ringkasan Tech Stack

| Layer                        | Teknologi      | Versi / Catatan                                       |
| :--------------------------- | :------------- | :---------------------------------------------------- |
| **Backend Framework**  | Laravel        | v13.x (PHP 8.4)                                       |
| **Frontend Framework** | Vue.js         | v3.5+ (Composition API + Inertia.js / Vite)           |
| **Database Engine**    | PostgreSQL     | v16+ (`JSONB` & *Partial Indexing*)               |
| **Web3 Client Engine** | Viem / Wagmi   | v2.x                                                  |
| **Smart Contract**     | Solidity       | v0.8.24 (EVM Compatible / Polygon / Arbitrum / Anvil) |
| **Real-time Server**   | Laravel Reverb | WebSocket Native                                      |
| **State Management**   | Pinia          | v2.x                                                  |

---

## 📚 Indeks Dokumentasi

- `GUIDELINES.md` — Standar arsitektur, alur SIWE, dan aturan penjangkaran *hash*.
- `STYLES.md` — Token desain UI Cyberpunk/Dark Enterprise, variabel warna, dan komponen status.
- `TABLES.md` — Skema tabel PostgreSQL, struktur `JSONB`, dan strategi pengindeksan.
- `FORMS.md` — Validasi formulir Laravel 13, pemprosesan berkas *client-side*, dan penandatanganan Web3.
- `GOALS.md` — Target sistem, peta jalan rekayasa Q3–Q4 2026, dan KPI.
