<?php

namespace App\Models;

use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'product_id',
        'collector_id',
        'payment_type',
        'total_amount',
        'paid_amount',
        'threshold_60_amount',
        'status',
        'client_signature', // <-- AJOUTER ICI
        'agent_signature',  // <-- AJOUTER ICI
        'signed_at',
        'protocol_terms',
        'delivered_approved_by_director',
        'delivered_at',
        'last_payment_at',
    ];

    protected $casts = [
        'delivered_approved_by_director' => 'boolean',
        'delivered_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'threshold_60_amount' => 'integer',
    ];

    /* =========================================================================
     | MÉTHODES MÉTIER & ATTRIBUTS CALCULÉS (60% - 40%)
     * ========================================================================= */

    /**
     * Calcule le pourcentage global de paiement effectué par le client.
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->paid_amount / $this->total_amount) * 100));
    }

    /**
     * Calcule le solde restant à payer (les 40% ou la part manquante).
     */
    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Vérifie si le client a franchi le seuil des 60% requis pour être éligible à la livraison.
     */
    public function isEligibleForDelivery(): bool
    {
        return $this->paid_amount >= $this->threshold_60_amount;
    }

    /**
     * Vérifie si la commande est totalement soldée (100%).
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Vérifie si le client livré est en retard de paiement sur le solde des 40%.
     * (Par exemple : aucun versement depuis plus de $days jours).
     */
    public function isOverdue(int $days = 14): bool
    {
        if ($this->status !== 'delivered' || !$this->last_payment_at) {
            return false;
        }

        return $this->last_payment_at->diffInDays(now()) > $days;
    }

    /* =========================================================================
     | RELATIONS ELOQUENT
     * ========================================================================= */

    /**
     * Le client qui a passé la commande.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Le produit / article concerné par la commande.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * L'agent / collecteur référent chargé du recouvrement auprès du client.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * L'historique des tranches de paiement encaissées pour cette commande.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class)->latest();
    }
}
