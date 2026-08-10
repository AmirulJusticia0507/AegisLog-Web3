<?php

declare(strict_types=1);

namespace App\Http\Requests\AuditLog;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,json,txt,log', 'max:51200'],
            'client_hash' => ['required', 'string', 'size:64', 'regex:/^[a-fA-F0-9]{64}$/'],
            'signature' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{130}$/'],
            'address' => ['required', 'string', 'size:42', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ];
    }
}
