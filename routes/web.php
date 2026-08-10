<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
Route::post('/audit-logs', [AuditLogController::class, 'store'])->name('audit-logs.store');
