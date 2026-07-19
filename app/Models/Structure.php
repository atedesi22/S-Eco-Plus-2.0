<?php

namespace App\Models;

use App\Models\Structure;
use App\Models\Tontine_plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

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
        'type',      // 'regional_direction' ou 'agency'
        'parent_id',  // Si type = 'agency', parent_id pointe vers la Direction Régionale
    ];

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
     * (Directeur Régional, Directeur d'Agence, Comptable, Secrétaire, etc.)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'structure_id');
    }

    /**
     * Un Scope local pour récupérer facilement uniquement les Directions Régionales.
     * Utilisation dans le code : Structure::regionalDirections()->get();
     */
    public function scopeRegionalDirections($query)
    {
        return $query->where('type', 'regional_direction');
    }

    /**
     * Un Scope local pour récupérer facilement uniquement les Agences.
     * Utilisation dans le code : Structure::agencies()->get();
     */
    public function scopeAgencies($query)
    {
        return $query->where('type', 'agency');
    }

//

}
