<?php

use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
Route::post('/audit-logs', [AuditLogController::class, 'store'])->name('audit-logs.store');
