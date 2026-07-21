<?php

namespace App\Models;

use App\Models\SubAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tontine_plan extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'default_color', 'is_active'];

    // Un plan possède plusieurs sous-comptes clients souscrits
    public function subAccounts()
    {
        return $this->hasMany(SubAccount::class);
    }

    /**
     * Boot du modèle pour intercepter la création.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            // Génère automatiquement un slug unique basé sur le nom du plan
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }
}
