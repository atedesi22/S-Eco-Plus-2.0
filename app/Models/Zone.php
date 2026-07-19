<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name'
    ];

    // La zone appartient à une agence parente
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    // Une zone contient plusieurs utilisateurs (ex: clients d'un marché spécifique)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
