<?php

namespace Database\Seeders;

use App\Models\PaymentGatewaySettings;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Заповнити базу даних базовими платіжними системами
     */
    public function run(): void
    {
        // Відновлення оригінальних даних з резервної копії

        // LiqPay - як було в оригіналі
        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'liqpay'],
            [
                'name' => 'LiqPay',
                'description' => 'Платіжна система ПриватБанку',
                'is_active' => true,
                'fee_percentage' => 2.75,
                'min_amount' => 1.00,
                'max_amount' => 999999.00,
                'currency' => 'UAH',
                'configuration' => [
                    'sandbox' => true,
                    'public_key' => '',
                    'private_key' => '',
                ],
            ]
        );

        // WayForPay - як було в оригіналі
        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'wayforpay'],
            [
                'name' => 'WayForPay',
                'description' => 'Універсальна платіжна система',
                'is_active' => true,
                'fee_percentage' => 2.50,
                'min_amount' => 1.00,
                'max_amount' => 999999.00,
                'currency' => 'UAH',
                'configuration' => [
                    'domain' => '',
                    'secret_key' => '',
                    'merchant_account' => '',
                ],
            ]
        );

        // Готівкою при отриманні - як було в оригіналі
        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'cash_on_delivery'],
            [
                'name' => 'Готівка при отриманні',
                'description' => 'Оплата готівкою кур\'єру або у відділенні',
                'is_active' => true,
                'fee_percentage' => 0.00,
                'min_amount' => 1.00,
                'max_amount' => 999999.00,
                'currency' => 'UAH',
                'configuration' => [],
            ]
        );

        // Банківський переказ - як було в оригіналі
        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name' => 'Банківський переказ',
                'description' => 'Оплата на банківську карту',
                'is_active' => true,
                'fee_percentage' => 0.00,
                'min_amount' => 1.00,
                'max_amount' => 999999.00,
                'currency' => 'UAH',
                'configuration' => [
                    'bank_details' => 'Приват24: 4149 4991 1234 5678',
                ],
            ]
        );

        $this->command->info('Original payment gateways restored successfully!');
    }
}
