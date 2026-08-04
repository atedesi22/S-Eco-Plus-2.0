<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDeposit extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'commercial_id',
        'agency_id',
        'cashier_id',
        'amount',
        'deposit_type',
        'status',
        'receipt_photo',
        'notes',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function agency()
    {
        return $this->belongsTo(Structure::class);
    }
}
