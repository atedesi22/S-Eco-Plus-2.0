<?php

namespace App\Models;

use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'target_value',
        'current_value',
        'period',
        'start_date',
        'end_date',
        'status'
    ];

    /**
     * Relation vers l'utilisateur concerné.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers les sanctions associées à cet objectif.
     */
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }
}
