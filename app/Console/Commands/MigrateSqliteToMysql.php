<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'db:migrate-sqlite-to-mysql
                            {--dry-run : Show what would be migrated without actually doing it}
                            {--table= : Migrate only a specific table}';

    protected $description = 'Migrate data from SQLite to MySQL';

    /**
     * Tables in dependency order (respecting foreign keys)
     */
    protected array $tableOrder = [
        // Base tables (no foreign keys)
        'users',
        'filter_groups',
        'shipping_providers',
        'payment_gateway_settings',
        'shop_settings',
        'display_settings',
        'seo_meta',
        'faq_pages',
        'media',
        'coupons',
        'brands',

        // First level dependencies
        'categories',
        'filters',
        'shipping_zones',
        'shipping_methods',
        'shipping_warehouses',

        // Second level dependencies
        'products',
        'category_filters',
        'brand_filters',
        'orders',

        // Third level dependencies
        'filter_products',
        'order_products',
        'coupon_usages',
        'reviews',
        'payments',
        'shipping_rates',
        'shipping_addresses',
        'shipments',

        // Fourth level dependencies
        'payment_logs',
        'tracking_updates',
    ];

    /**
     * Tables to skip (cache, session, job tables)
     */
    protected array $skipTables = [
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificTable = $this->option('table');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be migrated');
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('   SQLite to MySQL Migration Tool');
        $this->info('===========================================');
        $this->info('');

        // Verify connections
        $this->info('Verifying database connections...');

        try {
            DB::connection('sqlite_source')->getPdo();
            $this->info('  [OK] SQLite connection');
        } catch (\Exception $e) {
            $this->error('  [FAIL] SQLite connection: ' . $e->getMessage());
            return 1;
        }

        try {
            DB::connection('mysql')->getPdo();
            $this->info('  [OK] MySQL connection');
        } catch (\Exception $e) {
            $this->error('  [FAIL] MySQL connection: ' . $e->getMessage());
            return 1;
        }

        $this->info('');

        // Disable foreign key checks for MySQL
        if (!$dryRun) {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0');
        }

        $tables = $specificTable ? [$specificTable] : $this->tableOrder;
        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($tables as $table) {
            if (in_array($table, $this->skipTables)) {
                $this->line("  [SKIP] {$table} (system table)");
                $skipped++;
                continue;
            }

            $result = $this->migrateTable($table, $dryRun);
            if ($result === true) {
                $migrated++;
            } elseif ($result === null) {
                $skipped++;
            } else {
                $failed++;
            }
        }

        // Re-enable foreign key checks
        if (!$dryRun) {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('   Migration Summary');
        $this->info('===========================================');
        $this->info("  Migrated: {$migrated}");
        $this->info("  Skipped:  {$skipped}");
        $this->info("  Failed:   {$failed}");
        $this->info('');

        if ($failed > 0) {
            $this->error('Migration completed with errors!');
            return 1;
        }

        $this->info('Migration completed successfully!');
        return 0;
    }

    protected function migrateTable(string $table, bool $dryRun): ?bool
    {
        // Check if table exists in SQLite
        if (!Schema::connection('sqlite_source')->hasTable($table)) {
            $this->line("  [SKIP] {$table} (not in SQLite)");
            return null;
        }

        // Check if table exists in MySQL
        if (!Schema::connection('mysql')->hasTable($table)) {
            $this->line("  [SKIP] {$table} (not in MySQL)");
            return null;
        }

        $count = DB::connection('sqlite_source')->table($table)->count();

        if ($count === 0) {
            $this->line("  [SKIP] {$table} (0 records)");
            return null;
        }

        $this->info("  [MIGRATING] {$table}: {$count} records...");

        if ($dryRun) {
            return true;
        }

        try {
            // Truncate MySQL table first
            DB::connection('mysql')->table($table)->truncate();

            // Migrate in chunks to handle large tables
            DB::connection('sqlite_source')
                ->table($table)
                ->orderByRaw('1')
                ->chunk(100, function ($records) use ($table) {
                    $data = $records->map(function ($record) use ($table) {
                        return $this->transformRecord((array) $record, $table);
                    })->toArray();

                    if (!empty($data)) {
                        DB::connection('mysql')->table($table)->insert($data);
                    }
                });

            $newCount = DB::connection('mysql')->table($table)->count();
            $this->info("  [OK] {$table}: {$newCount} records migrated");
            return true;
        } catch (\Exception $e) {
            $this->error("  [FAIL] {$table}: " . $e->getMessage());
            return false;
        }
    }

    protected function transformRecord(array $record, string $table): array
    {
        // Handle boolean conversions
        $booleanFields = [
            'users' => ['is_admin'],
            'products' => ['is_hit', 'is_new', 'is_active'],
            'categories' => ['is_active'],
            'brands' => ['is_active'],
            'coupons' => ['is_active'],
            'reviews' => ['is_verified_purchase'],
            'filter_groups' => ['is_active'],
            'filters' => ['is_active'],
            'shipping_methods' => ['is_active'],
            'shipping_providers' => ['is_active'],
            'payment_gateway_settings' => ['is_active'],
            'shop_settings' => ['is_public'],
            'display_settings' => ['is_active'],
        ];

        if (isset($booleanFields[$table])) {
            foreach ($booleanFields[$table] as $field) {
                if (array_key_exists($field, $record)) {
                    $record[$field] = (int) filter_var($record[$field], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        // Handle JSON fields - validate they are proper JSON
        $jsonFields = [
            'products' => ['gallery', 'meta_keywords'],
            'display_settings' => ['value'],
            'shipping_methods' => ['settings'],
            'shipping_providers' => ['configuration'],
            'orders' => ['shipping_data'],
            'payments' => ['metadata', 'response_data'],
            'payment_logs' => ['request_data', 'response_data'],
        ];

        if (isset($jsonFields[$table])) {
            foreach ($jsonFields[$table] as $field) {
                if (isset($record[$field]) && is_string($record[$field])) {
                    $decoded = json_decode($record[$field]);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $record[$field] = null;
                    }
                }
            }
        }

        // Handle datetime fields - ensure proper format
        $dateFields = [
            'created_at',
            'updated_at',
            'deleted_at',
            'email_verified_at',
            'paid_at',
            'used_at',
            'valid_from',
            'valid_until',
            'event_time',
            'shipped_at',
            'delivered_at',
            'expires_at',
        ];

        foreach ($dateFields as $field) {
            if (isset($record[$field]) && $record[$field]) {
                $timestamp = strtotime($record[$field]);
                if ($timestamp !== false) {
                    $record[$field] = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $record[$field] = null;
                }
            }
        }

        return $record;
    }
}
