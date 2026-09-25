<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WalletSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function it_can_render_p2p_transfer_page_for_authenticated_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('wallet.transfer'));

        $response->assertStatus(200);
        $response->assertSee('Peer-to-Peer (P2P) Wallet Transfer', false);
    }

    /** @test */
    public function it_can_execute_p2p_wallet_transfer_between_users()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->deposit(500);

        $response = $this->actingAs($sender)->post(route('wallet.transfer.store'), [
            'email' => $recipient->email,
            'amount' => 150.00,
        ]);

        $response->assertRedirect(route('wallet.transfer'));

        $this->assertEquals(350.00, $sender->balance);
        $this->assertEquals(150.00, $recipient->balance);
    }

    /** @test */
    public function it_blocks_p2p_self_transfer()
    {
        $user = User::factory()->create();
        $user->deposit(500);

        $response = $this->actingAs($user)->post(route('wallet.transfer.store'), [
            'email' => $user->email,
            'amount' => 100.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(500.00, $user->balance);
    }

    /** @test */
    public function it_enforces_daily_spending_limit_policy()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $sender->deposit(10000);

        $response = $this->actingAs($sender)->post(route('wallet.transfer.store'), [
            'email' => $recipient->email,
            'amount' => 6000.00, // Exceeds $5,000 daily limit
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(10000.00, $sender->balance);
    }

    /** @test */
    public function it_can_render_financial_analytics_page()
    {
        $user = User::factory()->create();
        $user->deposit(200);

        $response = $this->actingAs($user)->get(route('wallet.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Financial Analytics & Cash Flow Intelligence', false);
    }

    /** @test */
    public function it_returns_json_analytics_data()
    {
        $user = User::factory()->create();
        $user->deposit(300);

        $response = $this->actingAs($user)->get(route('wallet.analytics.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'balance',
            'cash_in',
            'cash_out',
            'total_deposits',
            'total_withdrawals',
            'total_transfers',
            'trend_labels',
            'cash_in_trend',
            'cash_out_trend',
        ]);
    }
}
