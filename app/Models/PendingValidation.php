<?php

namespace App\Models;

use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingValidation extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'structure_id',
        'requested_by',
        'type',
        'description',
        'amount',
        'payload',
        'status',
        'validated_by',
        'validated_at',
    ];

    /**
     * Conversion automatique des données JSON et dates
     */
    protected $casts = [
        'payload' => 'array',
        'validated_at' => 'datetime',
    ];

    /**
     * Relation avec la structure
     */
    public function structure()
    {
        return $this->belongsTo(Structure::class);
    }

    /**
     * L'utilisateur qui a fait la demande
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * L'utilisateur qui a validé ou rejeté la demande
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
