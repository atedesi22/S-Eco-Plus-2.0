<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Account;
use App\Models\Agency;
use App\Models\Objective;
use App\Models\Product;
use App\Models\Sanction;
use App\Models\Structure;
use App\Models\SubAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Zone;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

        protected $fillable = [
            'name',
            'email',
            'phone',
            'password',
            'agency_id',
            'zone_id',
            'created_by',
            'structure_id',
            'collector_id',
            'mfa_enabled',
            'status',
            'profile_photo'
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gallery_images' => 'array',
        ];
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function subAccounts()
    {
        // Récupère les sous-comptes au travers de la table accounts
        return $this->hasManyThrough(SubAccount::class, Account::class);
    }

    /**
     * Scope pour filtrer uniquement les comptables
     */
    public function scopeComptables(Builder $query): void
    {
        $query->whereHas('roles', function ($q) {
            $q->where('name', 'Comptable');
        });
    }

    /**
     * Scope pour filtrer uniquement les secrétaires
     */
    public function scopeSecretaires(Builder $query): void
    {
        $query->whereHas('roles', function ($q) {
            $q->where('name', 'Secretaire');
        });
    }

    // Pour connaître le supérieur direct
    public function superior()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Pour connaître les personnes qui dépendent directement de cet utilisateur
    public function subordinates()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    /**
     * Les produits achetés par ce client (si l'utilisateur est un Client).
     */
    public function purchasedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_sales')
                    ->withPivot('quantity', 'total_price', 'performed_by')
                    ->withTimestamps();
    }

    /**
     * Les ventes de produits effectuées/validées par cet agent du staff.
     */
    public function managedSales()
    {
        return $this->hasMany(Transaction::class, 'performed_by');
        // Ou via la table pivot selon ton architecture finale
    }

    /**
     * Les transactions financières validées ou exécutées sur le terrain par cet agent.
     */
    public function transactionsAsValidator()
    {
        return $this->hasMany(Transaction::class, 'performed_by');
    }

    // --- RELATIONS CONTEXTE MANAGEMENT ---

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    /**
     * Récupère tous les rapports soumis par cet utilisateur (si modèle Report existant)
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    // --- ACTION SÉCURITÉ ---

    /**
     * Force la restauration du mot de passe à la valeur par défaut '0000'
     */
    public function resetPasswordToDefault(): bool
    {
        return $this->update([
            'password' => hash('sha256', '0000') // Ou Hash::make('0000') selon ta config
        ]);
    }

    /**
     * Les clients gérés par cet agent/gestionnaire
     */
    public function managedClients(): HasMany
    {
        // Ajustez 'collector_id' ou 'agent_id' selon le nom de votre clé étrangère dans la table clients/users
        return $this->hasMany(User::class, 'collector_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function tontines(): HasManyThrough
    {
        return $this->hasManyThrough(
            SubAccount::class, // Modèle final
            Account::class,    // Modèle intermédiaire
            'user_id',         // Clé étrangère dans la table accounts
            'account_id',      // Clé étrangère dans la table sub_accounts
            'id',              // Clé locale dans la table users
            'id'               // Clé locale dans la table accounts
        );
    }

    /**
     * Les prêts actifs gérés par cet agent
     */
    // public function activeLoans(): HasMany
    // {
    //     // Ajustez 'agent_id' et le statut 'active' selon votre table loans/credits
    //     return $this->hasMany(Loan::class, 'agent_id')->where('status', 'active');
    // }
}
