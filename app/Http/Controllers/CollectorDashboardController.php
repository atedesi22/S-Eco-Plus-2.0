<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Objective;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectorDashboardController extends Controller
{
    //
    public function index()
    {
        $collector = Auth::user();
        $today = now()->startOfDay();

        // 1. Total encaisse aujourd'hui sur le terrain
        $todayCollected = Transaction::where('performed_by', $collector->id)
            ->where('type', 'deposit')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        // 2. Nombre de passages / cotisations aujourd'hui
        $todayPassages = Transaction::where('performed_by', $collector->id)
            ->where('type', 'deposit')
            ->where('created_at', '>=', $today)
            ->count();

        // 3. Portefeuille clients attribues à cette collectrice
        $assignedClientsCount = User::where('collector_id', $collector->id)->count();

        // 4. Fond actuellement détenu sur soi (Collecté aujourd'hui - Déjà versé en caisse aujourd'hui)
        $todayDeposited = Transaction::where('performed_by', $collector->id)
            ->where('status', 'validated')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $cashHandheld = max(0, $todayCollected - $todayDeposited);

        // 5. Objectif de collecte mensuel
        $objective = Objective::where('type', 'collecte_amount')
            ->where('status', 'in_progress')
            ->where(function ($q) use ($collector) {
                $q->where('user_id', $collector->id)
                  ->orWhere('role_name', 'collectrice');
            })
            ->latest()
            ->first();

        $monthlyTarget = $objective ? $objective->target_value : 2000000; // 2 Millions par défaut
        $monthlyCollected = Transaction::where('performed_by', $collector->id)
            ->where('type', 'deposit')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        // 6. Dernières transactions sur le terrain
        $recentTransactions = Transaction::where('performed_by', $collector->id)
            ->with(['account.user', 'subAccount'])
            ->latest()
            ->take(5)
            ->get();

        return view('collector.dashboard', compact(
            'todayCollected',
            'todayPassages',
            'assignedClientsCount',
            'cashHandheld',
            'monthlyTarget',
            'monthlyCollected',
            'recentTransactions'
        ));
    }
}
