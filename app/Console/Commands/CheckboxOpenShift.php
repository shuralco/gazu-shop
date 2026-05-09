<?php

namespace App\Console\Commands;

use App\Services\Checkbox\CheckboxService;
use Illuminate\Console\Command;

class CheckboxOpenShift extends Command
{
    protected $signature = 'checkbox:open-shift';

    protected $description = 'Open a Checkbox.ua fiscal shift';

    public function handle(CheckboxService $checkbox): int
    {
        if (!$checkbox->isEnabled()) {
            $this->warn('Checkbox integration is disabled.');
            return self::SUCCESS;
        }

        $this->info('Opening Checkbox shift...');

        $result = $checkbox->openShift();

        if ($result) {
            $this->info('Shift opened successfully. ID: ' . ($result['id'] ?? 'N/A'));
            return self::SUCCESS;
        }

        $this->error('Failed to open shift. Check logs for details.');
        return self::FAILURE;
    }
}
