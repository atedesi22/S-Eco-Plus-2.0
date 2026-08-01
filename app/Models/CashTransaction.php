<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'agency_id',
        'user_id',          // Client ou bénéficiaire
        'operator_id',      // Caissier, Collecteur ou Directeur ayant effectué l'opération
        'type',             // 'credit' (entrée) ou 'debit' (sortie)
        'category',         // 'tontine_deposit', 'order_installment', 'cash_sale', 'client_withdrawal', 'cash_in', 'cash_out'
        'amount',
        'balance_after',
        'payment_method',   // 'cash', 'mobile_money', 'bank_transfer'
        'description',
        'reference_id',     // ID de commande ou de compte associé
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
