<?php

namespace App\Models;

use App\Models\CustomerTontine;
use App\Models\Sanction;
use App\Models\Transaction;
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
        'role_name',
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

    /**
     * Calcule dynamiquement la progression actuelle de l'objectif (Individuel ou de Groupe par Rôle)
     */
    public function getCurrentValueAttribute()
    {
        // Pool des agents concernés : soit un agent spécifique, soit tous les membres du rôle
        if ($this->role_name) {
            $userIds = User::role($this->role_name)->pluck('id')->toArray();
        } else {
            $userIds = array_filter([$this->user_id]);
        }

        if (empty($userIds)) return 0;

        switch ($this->type) {
            case 'new_tontines': // Tontines ouvertes sur le terrain par les agents
                return Transaction::whereIn('performed_by', $userIds)
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->count();

            case 'product_sales': // Articles de la boutique vendus par les agents
                return Transaction::whereIn('performed_by', $userIds)
                    ->where('type', 'product_payment')
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->sum('quantity');

            case 'collecte_amount': // Montant total des collectes effectuées
                return Transaction::whereIn('performed_by', $userIds)
                    ->where('type', 'deposit')
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->sum('amount');

            default:
                return 0;
        }
    }
}
