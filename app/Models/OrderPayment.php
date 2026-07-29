<?php

namespace App\Models;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'collected_by',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /* =========================================================================
     | RELATIONS ELOQUENT
     * ========================================================================= */

    /**
     * La commande à laquelle ce versement est rattaché.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * L'agent / collecteur ou caissier qui a enregistré l'encaissement.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
