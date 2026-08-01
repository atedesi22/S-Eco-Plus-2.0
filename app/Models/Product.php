<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'reference',
        'name',
        'description',
        'purchase_price',
        'selling_price_cash',         // Ajouté
        'selling_price_installment',  // Ajouté
        'stock',
        'agency_id',
        'alert_threshold',
        'agency_id',
        'is_available',
        'primary_image',
        'gallery_images'
    ];

    // AJOUTE CE BLOC ICI :
    protected $casts = [
        'gallery_images' => 'array',
    ];

    /**
     * Calcule la marge bénéficiaire brute sur ce produit.
     */
    public function getMarginAttribute(): int
    {
        return $this->selling_price - $this->purchase_price;
    }

    /**
     * Vérifie si le produit est en rupture de stock imminente.
     */
    public function isStockLow(): bool
    {
        return $this->stock <= $this->alert_threshold;
    }

    // --- RELATIONS ---

    /**
     * Un produit peut être associé à plusieurs transactions d'achat en boutique.
     * (Si tu crées une table pivot pour le panier d'achat des clients)
     */
    public function sales()
    {
        // Supposons une table pivot 'order_product' ou 'sale_product' plus tard
        return $this->belongsToMany(User::class, 'product_sales')
                    ->withPivot('quantity', 'total_price', 'performed_by')
                    ->withTimestamps();
    }

    /**
     * Un produit appartient à une agence.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
