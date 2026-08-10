**Fungsi:** Panduan visual UI, sistem warna Cyberpunk Dark Mode, dan *styling* status badge Tailwind CSS.


# Token Desain UI/UX & Panduan Gaya (AegisLog Web3)

AegisLog mengusung tema **Cyber-Security Dark Mode** yang bersih, fungsional, dan bernuansa *terminal enterprise*.

---

## 1. Palet Warna Utama

| Elemen                           | Kode Hex    | Kelas Tailwind CSS   | Fungsi Visual                               |
| :------------------------------- | :---------- | :------------------- | :------------------------------------------ |
| **Canvas Background**      | `#0a0e17` | `bg-slate-950`     | Latar belakang utama halaman                |
| **Panel / Card Surface**   | `#131b2e` | `bg-slate-900`     | Latar belakang wadah/komponen               |
| **Card Border**            | `#1e293b` | `border-slate-800` | Garis pembatas komponen                     |
| **Cyber Accent (Primary)** | `#6366f1` | `text-indigo-500`  | Tombol utama, batas aktif, ikon fokus       |
| **Status Valid (Green)**   | `#10b981` | `text-emerald-400` | Indikator integritas berkas cocok           |
| **Status Tampered (Red)**  | `#ef4444` | `text-red-500`     | Peringatan manipulasi /*hash mismatch*    |
| **Status Pending (Amber)** | `#f59e0b` | `text-amber-400`   | Transaksi penjangkaran*on-chain* berjalan |

---

## 2. Tipografi & Komponen Khas

- **Sans-Serif (Teks Umum)**: `Inter`, `-apple-system`, `sans-serif`
- **Monospace (Hash, Alamat Wallet, ID Log)**: `JetBrains Mono`, `Fira Code`, `monospace`

```html
<!-- Contoh Tampilan Hash SHA-256 pada Tabel Audit -->
<div class="flex items-center space-x-2 font-mono text-xs bg-slate-900/80 px-3 py-1.5 rounded border border-slate-800 text-slate-300">
  <span class="text-indigo-400 font-bold">SHA256:</span>
  <span class="truncate w-48">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>
</div>
```
