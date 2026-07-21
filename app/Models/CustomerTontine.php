<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTontine extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tontine_plan_id',
        'amount_to_reimburse',
        'amount_reimbursed',
        'deadline_date',
        'performed_by',
        'is_active'
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Relation vers le client titulaire de cette tontine.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation vers le plan de tontine global attaché.
     */
    public function plan()
    {
        return $this->belongsTo(Tontine_plan::class, 'tontine_plan_id');
    }

    /**
     * Relation vers l'agent qui a supervisé ou créé cette obligation financière.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope pour récupérer uniquement les tontines emprunts en situation d'impayé (échéance dépassée).
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_active', true)
                     ->where('deadline_date', '<', Carbon::now())
                     ->whereColumn('amount_reimbursed', '<', 'amount_to_reimburse');
    }
}
