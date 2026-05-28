<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Integrations\IntegrationManager;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check
                            {--threshold= : Override the default low stock threshold}
                            {--dry-run : Show low stock products without sending notifications}';

    protected $description = 'Check for products with low stock and send Telegram alerts';

    public function handle(): int
    {
        $defaultThreshold = (int) ($this->option('threshold') ?? 5);
        $dryRun = $this->option('dry-run');

        $products = Product::query()
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->whereRaw('quantity <= COALESCE(min_quantity, ?)', [$defaultThreshold])
            ->orderBy('quantity')
            ->get();

        if ($products->isEmpty()) {
            $this->info('All products have sufficient stock.');
            return self::SUCCESS;
        }

        $this->info("Found {$products->count()} product(s) with low stock:");

        $telegramEnabled = false;
        if (! $dryRun) {
            $telegram = app(IntegrationManager::class)->get('telegram');
            $telegramEnabled = $telegram && $telegram->isEnabled();
            $config = $telegram?->getConfig() ?? [];

            if (! ($config['notify_low_stock'] ?? true)) {
                $telegramEnabled = false;
            }
        }

        $sent = 0;
        $failed = 0;

        foreach ($products as $product) {
            $title = $product->getTranslation('title', 'uk', false)
                ?? $product->name
                ?? "ID: {$product->id}";
            $threshold = $product->min_quantity ?? $defaultThreshold;

            $this->line("  [{$product->sku}] {$title} — {$product->quantity}/{$threshold} шт.");

            if ($dryRun || ! $telegramEnabled) {
                continue;
            }

            $success = app(TelegramService::class)->sendLowStockAlert($product);
            if ($success) {
                $sent++;
            } else {
                $failed++;
            }

            // Rate limit: Telegram allows ~30 messages/second, stay safe
            usleep(100_000);
        }

        if ($dryRun) {
            $this->warn('Dry run — no notifications sent.');
        } elseif ($telegramEnabled) {
            $this->info("Telegram notifications sent: {$sent}, failed: {$failed}");
        }

        // Email digest fallback (always sends if email is configured)
        $email = \App\Models\DisplaySetting::get('low_stock_email')
            ?: \App\Models\DisplaySetting::get('header_email');
        if (! $dryRun && $email && ! filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)
                    ->send(new \App\Mail\LowStockAlertMail($products));
                $this->info("Email digest sent to {$email}");
            } catch (\Throwable $e) {
                $this->error("Email failed: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
