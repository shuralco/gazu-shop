<?php

namespace App\Console\Commands;

use App\Models\UpShipment;
use App\Services\UkrPoshtaEcomService;
use Illuminate\Console\Command;

class TrackUkrPoshtaShipments extends Command
{
    protected $signature = 'up:track
                            {--limit=100 : Max shipments per run}';

    protected $description = 'Pull StatusTracking updates for active UkrPoshta shipments';

    public function handle(UkrPoshtaEcomService $api): int
    {
        $shipments = UpShipment::needsTracking()
            ->where(function ($q) {
                $q->whereNull('last_tracked_at')
                    ->orWhere('last_tracked_at', '<', now()->subMinutes(20));
            })
            ->limit((int) $this->option('limit'))
            ->get();

        if ($shipments->isEmpty()) {
            $this->info('No shipments need tracking.');

            return self::SUCCESS;
        }

        $updated = 0;
        $errors = 0;

        foreach ($shipments as $sh) {
            $r = $api->getLastStatus($sh->ttn);

            if ($r['success'] && ! empty($r['response'])) {
                $resp = $r['response'];
                $code = (string) ($resp['statusCode'] ?? $resp['eventCode'] ?? '');
                $sh->update([
                    'up_status_text' => $resp['statusName'] ?? $resp['name'] ?? '',
                    'up_status_code' => $code,
                    'status' => $this->resolveStatus($code, $sh->status),
                    'last_tracked_at' => now(),
                ]);
                $updated++;
            } else {
                $sh->update(['last_tracked_at' => now()]);
                $errors++;
            }

            usleep(200_000); // be polite to the API
        }

        $this->info("Tracked: {$shipments->count()} (updated: {$updated}, errors: {$errors})");

        return self::SUCCESS;
    }

    /**
     * Map UkrPoshta status code → local lifecycle status.
     * Codes per UkrPoshta StatusTracking documentation.
     */
    private function resolveStatus(string $code, string $current): string
    {
        if (in_array($code, ['101', '102', '103'])) {
            return UpShipment::STATUS_DELIVERED;
        }
        if (in_array($code, ['501', '502', '503'])) {
            return UpShipment::STATUS_RETURNED;
        }
        if (in_array($code, ['201', '202'])) {
            return UpShipment::STATUS_ARRIVED;
        }
        if (in_array($code, ['1', '2', '3', '301', '302'])) {
            return UpShipment::STATUS_IN_TRANSIT;
        }
        if (in_array($code, ['41', '42', '43'])) {
            return UpShipment::STATUS_SENT;
        }

        return $current;
    }
}
