**Fungsi:** Aturan main pengembangan, diagram arsitektur hybrid, dan standar alur data.


# Panduan Pengembangan & Arsitektur AegisLog Web3

## 1. Topologi Arsitektur Hybrid (Off-Chain vs On-Chain)


[ Pengguna / Auditor ]
│
▼ (Vue 3 + Viem / Wagmi v2)
┌─────────────────────────────────────────────────────────────┐
│ 1. Scan / Unggah Berkas Laporan Audit (PDF/JSON)             │
│ 2. Hitung Hash SHA-256 di Browser                           │
│ 3. Sign Transaction via Wallet (MetaMask)                   │
└──────────────────────────────┬──────────────────────────────┘
│
┌───────────────┴───────────────┐
│                               │
▼ (Data Berkas + Signature)     ▼ (Hash SHA-256)
┌──────────────────────────────┐     ┌──────────────────────────────┐
│ Laravel 13 API Core          │     │ EVM Smart Contract           │
│  - Enkripsi Berkas (AES-256) │     │  - AuditVault.sol          │
│  - Simpan Meta ke PostgreSQL │     │  - Lock bytes32 Hash       │
│  - Reverb Real-time Push     │     │  - Emit Event LogAnchored  │
└──────────────┬───────────────┘     └──────────────┬───────────────┘
│                                    │
└──────────────────┬─────────────────┘
│ (Sync via Webhook / Queue)
▼
┌───────────────────────────┐
│ PostgreSQL 16+ Database   │
│  - Status: VALID / TAMPER │
└───────────────────────────┘

---

## 2. Aturan Utama Arsitektur

1. **Aturan Emas Privasi (Zero PII On-Chain)**: Dilarang keras mengunggah nama berkas asli, isi laporan, NIK, atau data pribadi pengguna ke dalam *smart contract*. Hanya *hash* `bytes32` (SHA-256) dan ID Unik (UUID) yang boleh berada di *ledger* blockchain.
2. **Kueri Tampilan Berbasis Off-Chain First**: Halaman *dashboard* Vue 3 tidak boleh memanggil *RPC node* blockchain secara berulang untuk menampilkan daftar laporan. Semua kueri membaca data terindeks dari PostgreSQL demi kecepatan respons (< 80ms).
3. **Pemberitahuan Real-Time Otomatis**: Jika pencocokan *hash* harian (*scheduled worker*) menemukan ketidakcocokan antara database dan *smart contract*, *job worker* wajib memicu *event* Laravel Reverb untuk menandai UI dengan status `TAMPERED`.

---

## 3. Standar Kode Backend (Laravel 13 & PHP 8.4)

- **Gunakan Fitur PHP 8.4**: Terapkan *property hooks*, *asymmetric visibility*, dan *strict typing* (`declare(strict_types=1);`).
- **Pola Action Class**: Pisahkan logika bisnis dari Controller. Contoh: `App\Actions\AuditLog\AnchorHashAction` dan `App\Actions\AuditLog\VerifyIntegrityAction`.
- **Integritas Transaksi**: Pembaharuan status audit wajib berada dalam blok `DB::transaction()`.
