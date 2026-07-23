<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSettlement extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'validated_by',
        'expected_amount',
        'declared_amount',
        'gap_amount',
        'status',
        'notes',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
