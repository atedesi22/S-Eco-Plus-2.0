<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'parent_id'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'parent_id');
    }

    /**
     * Obtenir les sous-agences / filiales rattachées
     */
    public function subAgencies(): HasMany
    {
        return $this->hasMany(Agency::class, 'parent_id');
    }

    /**
     * Obtenir les employés (Comptables, Secrétaires) de cette agence
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
