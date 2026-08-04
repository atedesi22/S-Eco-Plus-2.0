<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Agency;
use App\Models\CustomerTontine;
use App\Models\Order;
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
        'agency_id',
        'role_name',
        'user_id',
        'created_by',
        'title',
        'type',
        'target_value',
        'current_value',
        'base_bonus',
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

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cible dynamique avec majoration de +40% si Chef Commercial
     */
    public function getAdjustedTargetAttribute()
    {
        if ($this->role_name === 'chef_commercial' || ($this->user && $this->user->hasRole('chef_commercial'))) {
            return ceil($this->target_value * 1.40); // +40%
        }
        return $this->target_value;
    }

    /**
     * Calcul automatique de la progression dynamique
     */
    public function getCurrentValueAttribute()
    {
        $query = User::query();

        if ($this->user_id) {
            $userIds = [$this->user_id];
        } elseif ($this->role_name) {
            if ($this->agency_id) {
                $query->where('agency_id', $this->agency_id);
            }
            $userIds = $query->role($this->role_name)->pluck('id')->toArray();
        } else {
            return 0;
        }

        if (empty($userIds)) return 0;

        switch ($this->type) {
            case 'new_accounts':
                // Nombre de comptes clients créés
                return Account::whereIn('created_by', $userIds)
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->count();

            case 'collecte_amount':
                return Transaction::whereIn('performed_by', $userIds)
                    ->where('type', 'deposit')
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->sum('amount');

            case 'product_sales':
                return Order::whereIn('agent_id', $userIds)
                    ->whereBetween('created_at', [$this->start_date, $this->end_date])
                    ->count();

            default:
                return 0;
        }
    }

    /**
     * Calcul du pourcentage d'accomplissement
     */
    public function getProgressPercentageAttribute()
    {
        $target = $this->adjusted_target;
        if ($target <= 0) return 0;
        return round(($this->current_value / $target) * 100, 2);
    }

    /**
     * Calcul de la Prime conditionnée :
     * - < 70%  => 0 XAF
     * - 70% à 99% => 40% de la prime
     * - >= 100% => 100% de la prime
     */
    public function getCalculatedBonusAttribute()
    {
        $pct = $this->progress_percentage;
        $bonusBase = $this->base_bonus;

        if ($pct < 70) {
            return 0;
        } elseif ($pct >= 70 && $pct < 100) {
            return $bonusBase * 0.40; // 40% de la prime
        } else {
            return $bonusBase; // 100% de la prime
        }
    }
}
