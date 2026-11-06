<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Panel de monitoreo de autenticaciones (protegido).
     */
    public function __invoke(Request $request): View
    {
        $since = now()->subDay();

        $stats = [];
        foreach (['success', 'failed', 'blocked'] as $status) {
            $stats[$status] = LoginAttempt::where('status', $status)
                ->where('created_at', '>=', $since)
                ->count();
        }

        // IP con tres o más fallos en los últimos cinco minutos.
        $suspicious = LoginAttempt::query()
            ->select('ip_address', DB::raw('COUNT(*) as total_fallos'))
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('total_fallos')
            ->get();

        $recent = LoginAttempt::where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('panel', [
            'stats' => $stats,
            'suspicious' => $suspicious,
            'recent' => $recent,
            'user' => $request->user(),
        ]);
    }
}
