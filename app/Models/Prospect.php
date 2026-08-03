<?php

namespace App\Models;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'commercial_id',
        'agency_id',
        'zone_id',
        'full_name',
        'phone',
        'activity_sector',
        'location',
        'interest_type',
        'estimated_budget',
        'status',
        'notes',
        'next_contact_at',
        'converted_user_id',
    ];

    protected $casts = [
        'next_contact_at' => 'datetime',
        'estimated_budget' => 'decimal:2',
    ];

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function convertedUser()
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
