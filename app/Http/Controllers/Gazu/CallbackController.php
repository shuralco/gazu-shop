<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'nullable|string|max:100',
            'phone'  => 'required|string|min:7|max:32|regex:/^[\d\+\-\s\(\)]+$/',
            'source' => 'nullable|string|max:32',
        ], [
            'phone.required' => 'Введіть номер телефону',
            'phone.min'      => 'Номер телефону занадто короткий',
            'phone.regex'    => 'Невірний формат номера',
        ]);

        // Anti-spam: ліміт 3 на IP за 10 хвилин (через cache).
        $key = 'callback:'.$request->ip();
        $count = (int) \Cache::get($key, 0);
        if ($count >= 3) {
            return response()->json([
                'ok' => false,
                'message' => 'Забагато запитів. Спробуйте за 10 хвилин або зателефонуйте безпосередньо.',
            ], 429);
        }
        \Cache::put($key, $count + 1, now()->addMinutes(10));

        $req = CallbackRequest::create([
            'name'         => $data['name'] ?? null,
            'phone'        => $data['phone'],
            'source'       => $data['source'] ?? 'footer',
            'status'       => CallbackRequest::STATUS_NEW,
            'referrer_url' => mb_substr((string) $request->headers->get('referer', ''), 0, 500),
            'ip_address'   => $request->ip(),
            'user_agent'   => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $req->id,
            'message' => 'Дякуємо! Передзвонимо протягом 5 хвилин у робочий час.',
        ]);
    }
}
