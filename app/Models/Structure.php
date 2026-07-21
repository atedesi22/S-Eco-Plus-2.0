<?php

namespace App\Models;

use App\Models\Structure;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{

    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',        // 'regional_direction' ou 'agency'
        'parent_id',   // Si type = 'agency', pointe vers la Direction Régionale
        'director_id', // Identifiant du Directeur de la structure
    ];

    /**
     * Relation avec le Directeur / Responsable de la structure.
     */
    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Relation avec les Zones / Secteurs de collecte de l'agence.
     */
    public function zones()
    {
        return $this->hasMany(Zone::class, 'structure_id');
    }

    /**
     * Relation Réflexive : Récupérer la structure parente (Ex: la Direction Régionale d'une Agence).
     */
    public function parent()
    {
        return $this->belongsTo(Structure::class, 'parent_id');
    }

    /**
     * Relation Réflexive : Récupérer les structures enfants (Ex: toutes les Agences d'une Direction Régionale).
     */
    public function children()
    {
        return $this->hasMany(Structure::class, 'parent_id');
    }

    /**
     * Relation avec les Utilisateurs : Récupérer tout le personnel affecté à cette structure.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'structure_id');
    }

    /**
     * Scope local pour récupérer uniquement les Directions Régionales.
     */
    public function scopeRegionalDirections($query)
    {
        return $query->where('type', 'regional_direction');
    }

    /**
     * Scope local pour récupérer uniquement les Agences.
     */
    public function scopeAgencies($query)
    {
        return $query->where('type', 'agency');
    }

//

}
