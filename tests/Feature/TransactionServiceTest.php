<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Agency;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $agency;
    protected $staff;
    protected $client;
    protected $transactionService;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Créer une agence de test
        $this->agency = Agency::create([
            'name' => 'Agence Centrale Akwa',
            'code' => 'AG-AKWA-01',
            'location' => 'Douala'
        ]);

        // 2. Créer une secrétaire ou comptable connectée
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id
        ]);
        $this->actingAs($this->staff);

        // 3. Créer le client propriétaire du compte
        $this->client = User::factory()->create([
            'agency_id' => $this->agency->id
        ]);

        // 4. Instancier le service
        $this->transactionService = app(TransactionService::class);
    }

    /** @test */
    public function it_calculates_fees_correctly_for_a_10000_xaf_withdrawal()
    {
        // Compte avec 50 000 XAF de solde initial
        $account = Account::create([
            'user_id' => $this->client->id,
            'account_number' => 'ACC-10K-TEST',
            'type' => 'simple',
            'balance' => 50000.00,
            'reserve_fund' => 1000.00,
            'status' => 'active'
        ]);

        // Retrait de 10 000 XAF (1 palier = 150 XAF de frais)
        $transaction = $this->transactionService->withdraw($account->id, 10000.00, 'Test retrait 10K');

        $account->refresh();

        // 50 000 - 10 150 = 39 850
        $this->assertEquals(39850.00, $account->balance);
        $this->assertEquals(150.00, $transaction->fees);
        $this->assertEquals(10000.00, $transaction->amount);

        // Vérifier l'Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'account_id' => $account->id,
            'balance_before' => 50000.00,
            'balance_after' => 39850.00
        ]);
    }

    /** @test */
    public function it_calculates_fees_correctly_for_a_30000_xaf_withdrawal()
    {
        $account = Account::create([
            'user_id' => $this->client->id,
            'account_number' => 'ACC-30K-TEST',
            'type' => 'simple',
            'balance' => 50000.00,
            'reserve_fund' => 1000.00,
            'status' => 'active'
        ]);

        // Retrait de 30 000 XAF (2 paliers = 300 XAF de frais)
        $transaction = $this->transactionService->withdraw($account->id, 30000.00, 'Test retrait 30K');

        $account->refresh();

        // 50 000 - 30 300 = 19 700
        $this->assertEquals(19700.00, $account->balance);
        $this->assertEquals(300.00, $transaction->fees);
    }

    /** @test */
    public function it_refuses_withdrawal_if_it_violates_the_1000_xaf_reserve_fund()
    {
        $account = Account::create([
            'user_id' => $this->client->id,
            'account_number' => 'ACC-RESERVE-TEST',
            'type' => 'simple',
            'balance' => 11000.00, // Disponible réel : 10 000 XAF
            'reserve_fund' => 1000.00,
            'status' => 'active'
        ]);

        // Demande de 10 000 XAF + 150 XAF de frais = 10 150 XAF requis (alors que seulement 10 000 dispo)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Solde disponible insuffisant");

        $this->transactionService->withdraw($account->id, 10000.00);
    }
}
