<?php

namespace App\Console\Commands;

use App\Services\Checkbox\CheckboxService;
use Illuminate\Console\Command;

class CheckboxCloseShift extends Command
{
    protected $signature = 'checkbox:close-shift';

    protected $description = 'Close a Checkbox.ua fiscal shift';

    public function handle(CheckboxService $checkbox): int
    {
        if (!$checkbox->isEnabled()) {
            $this->warn('Checkbox integration is disabled.');
            return self::SUCCESS;
        }

        $this->info('Closing Checkbox shift...');

        $result = $checkbox->closeShift();

        if ($result) {
            $this->info('Shift closed successfully. ID: ' . ($result['id'] ?? 'N/A'));
            return self::SUCCESS;
        }

        $this->error('Failed to close shift. Check logs for details.');
        return self::FAILURE;
    }
}
