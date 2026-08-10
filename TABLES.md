
---
### 4. `TABLES.md`
> **Fungsi:** DDL dan skema struktur database PostgreSQL 16+ beserta indeks pencarian.

```markdown
# Skema Basis Data & Standar PostgreSQL (AegisLog Web3)

Basis data menggunakan **PostgreSQL 16+** dengan kombinasi tabel relasional dan kolom dokumen `JSONB` untuk fleksibilitas metadata forensik.
---
## 1. Struktural Tabel Utama

### `users` (Manajemen Pengguna & Wallet)

| Kolom              | Tipe Data       | Batasan           | Deskripsi                                  |
| :----------------- | :-------------- | :---------------- | :----------------------------------------- |
| `id`             | `uuid`        | PRIMARY KEY       | ID unik pengguna                           |
| `wallet_address` | `varchar(42)` | UNIQUE, INDEX     | Alamat*wallet* EVM (0x...)               |
| `role`           | `varchar(20)` | DEFAULT 'auditor' | Peran (`admin`, `auditor`, `viewer`) |
| `nonce`          | `varchar(64)` | NOT NULL          | *Nonce* kriptografi dinamis untuk SIWE   |
| `created_at`     | `timestamp`   | NOT NULL          | Waktu akun terdaftar                       |

### `audit_logs` (Pusat Berkas & Hash Log)

| Kolom                | Tipe Data        | Batasan           | Deskripsi                                          |
| :------------------- | :--------------- | :---------------- | :------------------------------------------------- |
| `id`               | `uuid`         | PRIMARY KEY       | ID log / UUID dokumen                              |
| `user_id`          | `uuid`         | FOREIGN KEY       | Pemilik / pengunggah log                           |
| `title`            | `varchar(255)` | NOT NULL          | Judul laporan audit                                |
| `file_path`        | `text`         | NOT NULL          | Jalur penyimpanan berkas terenkripsi               |
| `file_hash`        | `varchar(64)`  | INDEX, NOT NULL   | *Hash* SHA-256 berkas (`off-chain`)            |
| `tx_hash`          | `varchar(66)`  | NULLABLE, INDEX   | Hash transaksi*on-chain*                         |
| `block_number`     | `bigint`       | NULLABLE          | Nomor blok tempat*hash* dijangkarkan             |
| `integrity_status` | `varchar(20)`  | DEFAULT 'pending' | Status (`pending`, `verified`, `tampered`)   |
| `metadata`         | `jsonb`        | NOT NULL          | Detail forensik (ukuran berkas, tipe scanner, dll) |
| `created_at`       | `timestamp`    | NOT NULL          | Waktu unggah berkas                                |

---

## 2. Pengindeksan & Performa PostgreSQL

1. **Indeks GIN pada Metadata JSONB**:
   ```sql
   CREATE INDEX idx_audit_logs_metadata_gin ON audit_logs USING gin (metadata);
   ```

Indeks Parsial untuk Deteksi Tampering:

```SQL
CREATE INDEX idx_tampered_logs ON audit_logs (integrity_status) WHERE integrity_status = 'tampered';
```
