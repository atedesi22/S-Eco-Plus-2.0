<?php

namespace App\Models;

use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'structure_id',
        'name',
        'type',
        'assigned_to',
        'current_balance',
        'opening_balance',
        'max_limit',
        'status',
    ];

    /**
     * Relation avec la structure (agence)
     */
    public function structure()
    {
        return $this->belongsTo(Structure::class);
    }

    /**
     * Relation avec l'agent assigné à la caisse
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
