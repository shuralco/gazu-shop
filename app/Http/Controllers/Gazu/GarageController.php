<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\UserCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD для авто користувача у /gazu/garage.
 * Захист — middleware('auth') у routes/web.php (gazu.* group).
 */
class GarageController extends Controller
{
    public function __construct()
    {
        // Module flag: коли клієнт не оплатив — Гараж недоступний.
        // Рендеримо GAZU-styled 404 щоб не випадати на застарілий SimpleShop layout.
        if (! module('gazu_garage')->enabled()) {
            abort(response()->view('gazu.404', ['activeNav' => null], 404));
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $cars = $user->cars()->orderByDesc('is_primary')->orderByDesc('id')->get();

        return view('gazu.account.garage', [
            'user' => $user,
            'cars' => $cars,
            'activeNav' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCar($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            // Якщо вибрано primary — знімаємо прапорець з інших авто
            if (! empty($data['is_primary'])) {
                UserCar::where('user_id', $user->id)->update(['is_primary' => false]);
            }
            // Якщо це перше авто — авто-робимо його primary
            $isFirst = $user->cars()->count() === 0;

            UserCar::create(array_merge($data, [
                'user_id' => $user->id,
                'is_primary' => $isFirst || ($data['is_primary'] ?? false),
            ]));
        });

        return redirect()->route('gazu.garage')->with('flash_message', 'Авто додано');
    }

    public function update(Request $request, UserCar $car)
    {
        $this->authorize($car, $request);

        $data = $this->validateCar($request);

        DB::transaction(function () use ($car, $data) {
            if (! empty($data['is_primary']) && ! $car->is_primary) {
                UserCar::where('user_id', $car->user_id)
                    ->where('id', '!=', $car->id)
                    ->update(['is_primary' => false]);
            }
            $car->fill($data)->save();
        });

        return redirect()->route('gazu.garage')->with('flash_message', 'Авто оновлено');
    }

    public function destroy(Request $request, UserCar $car)
    {
        $this->authorize($car, $request);

        $wasPrimary = $car->is_primary;
        $userId = $car->user_id;
        $car->delete();

        // Якщо видалили primary, призначаємо primary найновішому з решти
        if ($wasPrimary) {
            $next = UserCar::where('user_id', $userId)->orderByDesc('id')->first();
            $next?->update(['is_primary' => true]);
        }

        return redirect()->route('gazu.garage')->with('flash_message', 'Авто видалено');
    }

    public function makePrimary(Request $request, UserCar $car)
    {
        $this->authorize($car, $request);
        $car->makePrimary();

        return redirect()->route('gazu.garage')->with('flash_message', 'Основне авто змінено');
    }

    private function validateCar(Request $request): array
    {
        return $request->validate([
            'make'      => 'required|string|max:60',
            'model'     => 'required|string|max:80',
            'year'      => 'nullable|integer|min:1950|max:'.(date('Y') + 1),
            'engine'    => 'nullable|string|max:80',
            'body_type' => 'nullable|string|max:60',
            'vin'       => 'nullable|string|max:30',
            'plate'     => 'nullable|string|max:20',
            'color'     => 'nullable|string|max:40',
            'is_primary'=> 'sometimes|boolean',
        ]);
    }

    private function authorize(UserCar $car, Request $request): void
    {
        if ($car->user_id !== $request->user()->id) {
            abort(403, 'Не ваше авто');
        }
    }
}
