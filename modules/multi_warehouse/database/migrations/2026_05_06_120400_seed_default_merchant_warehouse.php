<?php

use App\Models\DisplaySetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: create one default MerchantWarehouse using existing
 * NP/UP DisplaySettings, then seed Inventory rows for every product
 * that has products.quantity > 0.
 *
 * Idempotent: safe to re-run (skips if a default warehouse already exists).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('merchant_warehouses')->where('is_default', true)->exists()) {
            return;
        }

        $now = now();

        $warehouseId = DB::table('merchant_warehouses')->insertGetId([
            'code' => 'MAIN-01',
            'name' => 'Головний склад',
            'type' => 'own',
            'country' => 'UA',
            'is_active' => true,
            'is_default' => true,
            'pickup_supported' => false,
            'sort_order' => 0,
            'np_sender_ref' => DisplaySetting::get('np_sender_ref') ?: null,
            'np_sender_city_ref' => DisplaySetting::get('np_sender_city_ref') ?: null,
            'np_sender_warehouse_ref' => DisplaySetting::get('np_sender_warehouse_ref') ?: null,
            'np_contact_person_ref' => DisplaySetting::get('np_contact_person_ref') ?: null,
            'np_sender_phone' => DisplaySetting::get('np_sender_phone') ?: null,
            'up_sender_uuid' => DisplaySetting::get('up_sender_uuid') ?: null,
            'up_sender_address_uuid' => DisplaySetting::get('up_sender_address_uuid') ?: null,
            'up_counterparty_token' => DisplaySetting::get('up_counterparty_token') ?: null,
            'up_ecom_bearer' => DisplaySetting::get('up_ecom_bearer') ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Seed inventory: copy products.quantity into the default warehouse.
        DB::table('products')
            ->select('id', 'quantity')
            ->where('quantity', '>', 0)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($warehouseId, $now) {
                $batch = [];
                foreach ($rows as $row) {
                    $batch[] = [
                        'product_id' => $row->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => (int) $row->quantity,
                        'reserved_quantity' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($batch) {
                    DB::table('inventory')->insert($batch);
                }
            });

        // Backfill orders.warehouse_id for existing orders → default warehouse.
        DB::table('orders')->whereNull('warehouse_id')->update(['warehouse_id' => $warehouseId]);
        DB::table('order_products')->whereNull('warehouse_id')->update(['warehouse_id' => $warehouseId]);
    }

    public function down(): void
    {
        // Reverse only the backfill — keep the warehouse and inventory rows.
        DB::table('orders')->whereNotNull('warehouse_id')->update(['warehouse_id' => null]);
        DB::table('order_products')->whereNotNull('warehouse_id')->update(['warehouse_id' => null]);
    }
};
