<?php

namespace App\Http\Controllers;

use App\Events\NpShipmentStatusChanged;
use App\Models\NpShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives webhook payloads from Nova Poshta about TTN status changes.
 * Push updates avoid polling overhead — pair with np:track as fallback.
 *
 * Configure in NP cabinet: Settings → Notifications → Webhook URL
 *   POST https://your-shop/api/np-webhook?secret=XXX
 *
 * Set NP_WEBHOOK_SECRET in .env to validate.
 */
class NpWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->all();
        $ttn = $payload['DocumentNumber'] ?? $payload['Number'] ?? null;
        $statusCode = $payload['StatusCode'] ?? null;

        // Audit-log ALL incoming webhooks
        $logEntry = \App\Models\NpWebhookLog::create([
            'ttn' => $ttn,
            'status_code' => $statusCode,
            'status' => $payload['Status'] ?? null,
            'payload' => $payload,
            'signature_valid' => true,
            'processed' => false,
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        // Validate shared secret (or HMAC X-NP-Signature header)
        $expected = config('novaposhta.webhook_secret') ?: env('NP_WEBHOOK_SECRET');
        if ($expected) {
            $providedSecret = $request->query('secret');
            $providedSig = $request->header('X-NP-Signature');

            $secretOk = $providedSecret && hash_equals($expected, $providedSecret);
            $hmacOk = false;
            if ($providedSig) {
                $expectedHmac = hash_hmac('sha256', $request->getContent(), $expected);
                $hmacOk = hash_equals($expectedHmac, $providedSig);
            }

            if (! $secretOk && ! $hmacOk) {
                $logEntry->update(['signature_valid' => false, 'error' => 'invalid signature']);
                Log::warning('NP webhook: invalid secret/signature', ['ip' => $request->ip()]);
                return response()->json(['error' => 'forbidden'], 403);
            }
        }

        if (! $ttn) {
            $logEntry->update(['error' => 'no DocumentNumber']);
            return response()->json(['error' => 'no DocumentNumber'], 422);
        }

        $shipment = NpShipment::where('ttn', $ttn)->first();
        if (! $shipment) {
            $logEntry->update(['processed' => true, 'error' => 'shipment not in DB']);
            Log::info("NP webhook: shipment not found for TTN {$ttn}");
            return response()->json(['ok' => true, 'note' => 'shipment not in DB']);
        }

        $oldStatus = $shipment->status;
        $newStatus = NpShipment::resolveStatusFromCode($statusCode);

        $history = $shipment->tracking_history ?? [];
        $history[] = [
            'status' => $payload['Status'] ?? '',
            'status_code' => $statusCode,
            'date' => $payload['DateTime'] ?? now()->toDateTimeString(),
            'source' => 'webhook',
        ];

        $shipment->update([
            'status' => $newStatus,
            'np_status' => $payload['Status'] ?? $shipment->np_status,
            'np_status_code' => $statusCode,
            'tracking_history' => $history,
            'last_tracked_at' => now(),
        ]);

        if ($oldStatus !== $newStatus) {
            event(new NpShipmentStatusChanged($shipment->fresh(), $oldStatus, $newStatus));
        }

        $logEntry->update(['processed' => true]);
        return response()->json(['ok' => true]);
    }
}
