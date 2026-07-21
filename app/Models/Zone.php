<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'structure_id',
        'manager_id',
        'is_active',
    ];


    // L'agence à laquelle appartient la zone
    public function agency()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    // Le Chef Commercial / Chef de Zone
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Tout le personnel terrain et clients affectés à cette zone
    public function members()
    {
        return $this->hasMany(User::class, 'zone_id');
    }

    // Filtrer les collectrices/commerciaux de la zone
    public function agents()
    {
        return $this->hasMany(User::class, 'zone_id')->withoutRole('Client');
    }

    // Filtrer uniquement les clients de la zone
    public function clients()
    {
        return $this->hasMany(User::class, 'zone_id')->role('Client');
    }
}
