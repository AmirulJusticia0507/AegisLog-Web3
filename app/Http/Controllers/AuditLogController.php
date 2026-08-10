<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AuditLog\StoreAuditLogAction;
use App\Http\Requests\AuditLog\StoreAuditLogRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('AuditLogs/Index', [
            'auditLogs' => AuditLog::with('user')->latest('created_at')->get(),
        ]);
    }

    public function store(StoreAuditLogRequest $request, StoreAuditLogAction $action): RedirectResponse
    {
        $log = $action->execute($request->file('file'), $request->validated());

        return redirect()
            ->route('audit-logs.index')
            ->with('flash', [
                'status' => 'success',
                'message' => "Berkas \"{$log->title}\" diterima. Hash SHA-256 terverifikasi dan menunggu penjangkaran on-chain.",
            ]);
    }
}
