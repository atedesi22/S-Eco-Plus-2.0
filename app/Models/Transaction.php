<?php

namespace App\Models;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'account_id',
        'performed_by',
        'type',
        'amount',
        'fees',
        'reference',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
    ];

    // La transaction impacte un sous-compte/tontine
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // La transaction a été opérée par un utilisateur spécifique (Collectrice, Commercial, Admin, etc.)
    public function operator()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Si la transaction concerne l'achat d'un produit physique.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
