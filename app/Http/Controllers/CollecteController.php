<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class CollecteController extends Controller
{
    //
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function encaisserTontine(Request $request)
    {
        try {
            // Applique le dépôt en une seule ligne sécurisée
            $this->transactionService->deposit(
                $request->account_id,
                $request->amount,
                "Collecte mensuelle effectuée sur le terrain."
            );

            return back()->with('success', 'Dépôt sécurisé et validé avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
