<?php

namespace App\Models;

use App\Models\SubAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
