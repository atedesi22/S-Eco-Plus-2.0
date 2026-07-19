<?php

namespace App\Models;

use App\Models\SubAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'type',
        'balance',
        'reserve_fund',
        'locked_until',
        'status'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'reserve_fund' => 'decimal:2',
        'locked_until' => 'date',
    ];

    // Le compte appartient à un client (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation : Un compte principal a plusieurs sous-comptes / tontines
    public function subAccounts()
    {
        return $this->hasMany(SubAccount::class); // Vérifie si ton modèle s'appelle bien SubAccount ou Tontine
    }

    // Relation : Un compte principal a plusieurs transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope ou méthode d'aide pour savoir si le compte est actuellement bloqué aux retraits
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
