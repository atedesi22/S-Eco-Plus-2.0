<?php

namespace App\Models;

use App\Models\Objective;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sanction extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'objective_id',
        'reason',
        'severity',
        'financial_penalty_amount',
        'applied_at',
        'is_active'
    ];

    /**
     * Relation vers l'utilisateur sanctionné.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers l'objectif à l'origine de la sanction.
     */
    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }
}
