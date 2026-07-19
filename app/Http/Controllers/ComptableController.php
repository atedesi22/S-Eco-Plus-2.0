<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComptableController extends Controller
{
    //
    public function index()
    {
        $agencyId = Auth::user()->agency_id;

        // Chiffres clés de l'agence du comptable
        $totalBalance = Account::whereHas('user', function($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->sum('balance');

        $totalReserve = Account::whereHas('user', function($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->sum('reserve_fund');

        return view('dashboard', compact('totalBalance', 'totalReserve'));
    }
}
