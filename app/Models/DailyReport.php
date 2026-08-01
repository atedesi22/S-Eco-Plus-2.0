<?php

namespace App\Models;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'report_date',
        'total_field_collections',  // Total collecté par les agents sur le terrain
        'total_cash_sales',         // Total ventes boutique au comptant
        'total_collected',          // Somme globale encaissee
        'new_clients_count',        // Nouveaux clients enregistrés ce jour
        'deliveries_count',         // Articles livrés ce jour (seuil 60%)
        'notes',
        'validated_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_field_collections' => 'integer',
        'total_cash_sales' => 'integer',
        'total_collected' => 'integer',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
