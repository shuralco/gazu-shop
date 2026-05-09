<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Create payments for existing orders
        $orders = Order::where('status', 'paid')->take(10)->get();

        foreach ($orders as $order) {
            Payment::create([
                'order_id' => $order->id,
                'gateway' => 'liqpay',
                'gateway_transaction_id' => 'test_'.uniqid(),
                'amount' => $order->total,
                'currency' => 'UAH',
                'status' => 'completed',
                'gateway_response' => json_encode([
                    'status' => 'success',
                    'transaction_id' => 'test_'.uniqid(),
                    'amount' => $order->total,
                ]),
                'processed_at' => now(),
            ]);
        }

        // Create some test payments
        Payment::factory(20)->create();

        $this->command->info('Payments seeded: '.Payment::count());
    }
}
