<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'email'      => 'required|email|max:120',
            'phone'      => 'nullable|string|max:32',
            'name'       => 'nullable|string|max:100',
        ], [
            'email.required' => 'Введіть email',
            'email.email'    => 'Невалідний email',
        ]);

        // Anti-spam: 5 на IP за 10 хв
        $key = 'stocknotify:'.$request->ip();
        if ((int) \Cache::get($key, 0) >= 5) {
            return response()->json([
                'ok' => false,
                'message' => 'Забагато запитів. Спробуйте за 10 хвилин.',
            ], 429);
        }
        \Cache::put($key, (int) \Cache::get($key, 0) + 1, now()->addMinutes(10));

        $notif = StockNotification::updateOrCreate(
            ['product_id' => (int) $data['product_id'], 'email' => $data['email']],
            [
                'phone' => $data['phone'] ?? null,
                'name'  => $data['name'] ?? null,
                'notified' => false,
                'notified_at' => null,
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Сповістимо вас одразу як товар з\'явиться у наявності.',
        ]);
    }
}
