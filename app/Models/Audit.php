<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    //
    // Bloquer les modifications : un log d'audit ne doit JAMAIS être modifié (sécurité)
    protected $fillable = [
        'action',
        'user_id',
        'agency_id',
        'account_id',
        'balance_before',
        'balance_after',
        'ip_address',
        'user_agent'
    ];

    /**
     * L'utilisateur (employé ou agent) qui a déclenché l'action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'agence dans laquelle l'action a été enregistrée
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Le compte financier concerné par la modification de solde
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
