<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Tontine_plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccount extends Model
{
    //
    use HasFactory;

    protected $fillable = [
    'account_id',
    'tontine_plan_id', // <-- Ajoute cette ligne
    'name',
    'code',
    'balance',
    'target_amount',
    'color',
    'status',
];

/**
     * Relation : Un sous-compte appartient à un plan de tontine spécifique.
     */
    public function plan()
    {
        return $this->belongsTo(Tontine_plan::class, 'tontine_plan_id');
    }

    /**
     * Relation : Un sous-compte appartient à un compte principal.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
