<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testCreateDepositForUser()
    {
        $response = $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 150]);
        $response->assertStatus(200)->assertJson(['amount' => 150, 'type' => 'deposit', 'user_id' => $this->user->id]);
        $this->assertDatabaseCount('transactions', 1);

        $response = $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 150.03]);
        $response->assertStatus(200)->assertJson(['amount' => 150.03, 'type' => 'deposit', 'user_id' => $this->user->id]);
        $this->assertDatabaseCount('transactions', 2);

        $this->user->refresh();
        self::assertEquals(300.03, $this->user->balance);
    }

    public function testCreateWithdrawForUser()
    {
        $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 150]);
        $response = $this->postJson("api/users/{$this->user->id}/withdraw", ['amount' => 100]);

        $response->assertStatus(200)->assertJson(['amount' => -100, 'type' => 'withdraw', 'user_id' => $this->user->id]);

        $this->assertDatabaseCount('transactions', 2);

        $this->user->refresh();
        self::assertEquals(50, $this->user->balance);
    }

    public function testBonusBalanceOnDeposit()
    {
        $amount = 110;
        $times = 8;
        $bonusTimes = floor($times % 3);

        Transaction::factory()->count($times)->for($this->user)->deposit()->state(function (array $attributes) use ($amount) {
            return [
                'amount' => $amount,
            ];
        })->create();

        $this->assertDatabaseCount('transactions', 8);

        self::assertEquals($amount * $times, $this->user->balance);
        self::assertEquals($amount * $this->user->bonus * $bonusTimes, $this->user->bonus_balance);
    }

    public function testMinimalAmountValue()
    {
        $response = $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => -1]);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);

        $response = $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 0]);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    public function testRequiredAmount()
    {
        $response = $this->postJson("api/users/{$this->user->id}/deposit", []);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    public function testNumericAmount()
    {
        $response = $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 'test']);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    public function testInsufficientFunds()
    {
        $this->postJson("api/users/{$this->user->id}/deposit", ['amount' => 150]);
        $response = $this->postJson("api/users/{$this->user->id}/withdraw", ['amount' => 200]);

        $response->assertStatus(422)->assertJson(['message' => 'Insufficient funds to finish the operation.']);
    }
}
