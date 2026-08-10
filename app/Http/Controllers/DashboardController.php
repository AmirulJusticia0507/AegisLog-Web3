<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $total = AuditLog::count();
        $anchored = AuditLog::where('integrity_status', 'verified')->count();
        $pending = AuditLog::where('integrity_status', 'pending')->count();
        $tampered = AuditLog::where('integrity_status', 'tampered')->count();

        $thirtyDays = Carbon::now()->subDays(29)->startOfDay();

        $dayExpression = app('db')->getDriverName() === 'pgsql'
            ? "to_char(created_at, 'YYYY-MM-DD')"
            : "strftime('%Y-%m-%d', created_at)";

        $trend = AuditLog::query()
            ->where('created_at', '>=', $thirtyDays)
            ->selectRaw("{$dayExpression} as day")
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when integrity_status = 'tampered' then 1 else 0 end) as tampered")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day')
            ->mapWithKeys(function ($row) {
                return [$row['day'] => [
                    'total' => (int) $row['total'],
                    'tampered' => (int) $row['tampered'],
                ]];
            });

        $chart = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = $thirtyDays->copy()->addDays($i)->toDateString();
            $chart[$day] = $trend[$day] ?? ['total' => 0, 'tampered' => 0];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $total,
                'anchored' => $anchored,
                'pending' => $pending,
                'tampered' => $tampered,
            ],
            'chart' => $chart,
            'recentLogs' => AuditLog::with('user')
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
