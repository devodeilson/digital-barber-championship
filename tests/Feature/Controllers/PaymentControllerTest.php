<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Services\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_checkout()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        
        $this->actingAs($user)
            ->get(route('payment.checkout', $transaction))
            ->assertStatus(302)
            ->assertRedirect();
    }

    public function test_cannot_checkout_non_pending_transaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed'
        ]);
        
        $this->actingAs($user)
            ->get(route('payment.checkout', $transaction))
            ->assertSessionHas('error');
    }

    public function test_can_handle_payment_success()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        
        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('getPaymentInfo')
                ->once()
                ->andReturn(['status' => 'succeeded']);
        });
        
        $this->actingAs($user)
            ->get(route('payment.success', $transaction))
            ->assertSessionHas('success');
            
        $this->assertEquals('completed', $transaction->fresh()->status);
    }

    public function test_can_handle_payment_cancel()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        
        $this->actingAs($user)
            ->get(route('payment.cancel', $transaction))
            ->assertSessionHas('info');
            
        $this->assertEquals('failed', $transaction->fresh()->status);
    }

    public function test_can_handle_webhook()
    {
        $transaction = Transaction::factory()->create([
            'payment_id' => 'pay_123',
            'status' => 'pending'
        ]);

        $payload = [
            'type' => 'payment.succeeded',
            'data' => ['id' => 'pay_123']
        ];

        $this->mock(PaymentGateway::class, function ($mock) use ($payload) {
            $mock->shouldReceive('handleWebhook')
                ->once()
                ->with($payload)
                ->andReturn(true);
        });

        $this->postJson(route('payment.webhook'), $payload)
            ->assertStatus(200);
    }
} 