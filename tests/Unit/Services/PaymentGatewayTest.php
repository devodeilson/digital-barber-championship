<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new PaymentGateway();
    }

    public function test_can_create_payment()
    {
        Http::fake([
            '*/payments' => Http::response([
                'id' => 'pay_123',
                'url' => 'https://payment.test/checkout'
            ], 200)
        ]);

        $transaction = Transaction::factory()->create();
        
        $payment = $this->gateway->createPayment($transaction);
        
        $this->assertEquals('pay_123', $payment['id']);
        $this->assertEquals('https://payment.test/checkout', $payment['url']);
    }

    public function test_handles_payment_creation_error()
    {
        Http::fake([
            '*/payments' => Http::response([
                'message' => 'Invalid amount'
            ], 400)
        ]);

        $transaction = Transaction::factory()->create();
        
        $this->expectException(\Exception::class);
        
        $this->gateway->createPayment($transaction);
    }

    public function test_can_get_payment_info()
    {
        Http::fake([
            '*/payments/*' => Http::response([
                'id' => 'pay_123',
                'status' => 'succeeded'
            ], 200)
        ]);

        $info = $this->gateway->getPaymentInfo('pay_123');
        
        $this->assertEquals('succeeded', $info['status']);
    }

    public function test_can_process_refund()
    {
        Http::fake([
            '*/refunds' => Http::response([
                'id' => 'ref_123',
                'status' => 'succeeded'
            ], 200)
        ]);

        $refund = $this->gateway->refund('pay_123');
        
        $this->assertEquals('succeeded', $refund['status']);
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

        $this->gateway->handleWebhook($payload);
        
        $this->assertEquals('completed', $transaction->fresh()->status);
    }
} 