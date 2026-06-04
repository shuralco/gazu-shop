<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core seeders
        $this->call([
            UserSeeder::class,           // Users and admins
            CategorySeeder::class,        // Product categories
            ProductSeeder::class,         // Products
            FilterSeeder::class,          // Product filters
            ElectronicsFilterSeeder::class, // Electronics specific filters
            MediaSeeder::class,           // Media files
        ]);

        // E-commerce configuration
        $this->call([
            ShippingSeeder::class,        // Shipping providers
            ShippingMethodSeeder::class,  // Shipping methods
            CouponSeeder::class,          // Discount coupons
            ShopSettingsSeeder::class,    // Shop configuration
            PaymentGatewaySeeder::class,  // Payment gateways
            PaymentSeeder::class,         // Sample payments
            DisplaySettingsSeeder::class, // Display and UI settings
            SeoMetaSeeder::class,         // SEO meta data
            HomepageModuleSeeder::class,  // Homepage page builder modules
        ]);

        // Customer groups & loyalty
        $this->call([
            CustomerGroupSeeder::class,   // Customer groups
            LoyaltyTierSeeder::class,     // Loyalty tiers
            LoyaltySettingsSeeder::class, // Loyalty settings
        ]);

        // Admin access control
        $this->call([
            AccessPresetSeeder::class,    // RBAC presets (roles)
        ]);

        // Test data
        $this->call([
            TestOrdersSeeder::class,      // Sample orders
            ReviewSeeder::class,          // Product reviews
        ]);

        $this->command->info('');
        $this->command->info('=================================');
        $this->command->info('Database seeding completed!');
        $this->command->info('=================================');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', \App\Models\User::count()],
                ['Categories', \App\Models\Category::count()],
                ['Products', \App\Models\Product::count()],
                ['Orders', \App\Models\Order::count()],
                ['Payments', \App\Models\Payment::count()],
                ['Coupons', \App\Models\Coupon::count()],
                ['Filter Groups', \App\Models\FilterGroup::count()],
                ['Filters', \App\Models\Filter::count()],
                ['Display Settings', \App\Models\DisplaySetting::count()],
                ['SEO Meta', \App\Models\SeoMeta::count()],
                ['Media Files', \App\Models\Media::count()],
                ['Reviews', \App\Models\Review::count()],
                ['Customer Groups', \App\Models\CustomerGroup::count()],
                ['Loyalty Tiers', \App\Models\LoyaltyTier::count()],
            ]
        );
    }
}
