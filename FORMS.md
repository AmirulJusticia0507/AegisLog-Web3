
---
### 5. `FORMS.md`
> **Fungsi:** Aturan validasi Form Request Laravel 13 dan logika pembuatan *hash client-side* di Vue 3.

```markdown
# Penanganan Formulir & Validasi Kriptografi (AegisLog Web3)

Alur penanganan formulir di AegisLog menggabungkan penghitungan *hash* berkas di sisi klien (*Vue 3*), penandatanganan *wallet*, dan validasi ketat di backend (*Laravel 13*).
---
## 1. Validasi Form Request Laravel 13

```php
namespace App\Http\Requests\AuditLog;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'file'         => ['required', 'file', 'mimes:pdf,json,txt,log', 'max:51200'], // Maks 50MB
            'client_hash'  => ['required', 'string', 'size:64', 'regex:/^[a-fA-F0-9]{64}$/'],
            'signature'    => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{130}$/'],
        ];
    }
}
2. Alur Klien Vue 3 (Unggah & Hash Calculation)
Perhitungan Hash Lokal: Sebelum berkas dikirim ke server, Vue 3 menggunakan Web Crypto API (crypto.subtle.digest('SHA-256', arrayBuffer)) untuk menghitung hash di komputer pengguna.

Minta Penandatanganan Wallet: Tampilkan modal pratinjau yang berisi Judul Berkas, Ukuran, dan Hash SHA-256. Pengguna menyetujui transaksi penjangkaran melalui MetaMask.

Kirim Payload Lengkap: Vue 3 mengunggah berkas mentah, client_hash, dan signature ke API Laravel 13.

Verifikasi Silang Backend: Laravel menghitung ulang hash berkas yang diterima. Jika server_hash !== client_hash, permintaan ditolak (HTTP 422).
```
