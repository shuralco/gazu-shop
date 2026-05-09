<?php

namespace App\Console\Commands;

use App\Services\Integrations\IntegrationManager;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTest extends Command
{
    protected $signature = 'telegram:test
                            {message? : Custom test message to send}';

    protected $description = 'Send a test message to verify Telegram bot configuration';

    public function handle(): int
    {
        $service = app(TelegramService::class);

        if (! $service->isConfigured()) {
            $this->error('Telegram bot is not configured.');
            $this->line('');
            $this->line('Configure via one of:');
            $this->line('  1. Admin panel → Integrations → Telegram Bot');
            $this->line('  2. Environment variables TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID');
            return self::FAILURE;
        }

        $telegram = app(IntegrationManager::class)->get('telegram');
        $isEnabled = $telegram && $telegram->isEnabled();

        if (! $isEnabled) {
            $this->warn('Telegram integration is disabled in settings, but sending test message anyway...');
        }

        $message = $this->argument('message')
            ?? '✅ SimpleShop — Telegram бот працює! Тестове повідомлення надіслано о '
            . now()->format('d.m.Y H:i:s');

        $this->info('Sending test message...');

        $success = $service->send($message);

        if ($success) {
            $this->info('Test message sent successfully!');
            return self::SUCCESS;
        }

        $this->error('Failed to send test message. Check logs for details.');
        return self::FAILURE;
    }
}
