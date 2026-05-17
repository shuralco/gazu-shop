<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $raw = (string) ($request->cookie('gazu_compare', ''));
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)))));
        $ids = array_slice($ids, 0, 4);

        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->with('brand')
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();

        // Extract spec rows для table — union all specifications.
        $specRows = [];
        foreach ($products as $p) {
            $specs = is_array($p->specifications) ? $p->specifications : (json_decode($p->specifications ?? '[]', true) ?: []);
            foreach ($specs as $k => $v) {
                if (! is_string($k)) continue;
                if (! isset($specRows[$k])) $specRows[$k] = [];
                $specRows[$k][$p->id] = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }

        return view('gazu.compare', [
            'products' => $products,
            'specRows' => $specRows,
            'activeNav' => null,
        ]);
    }
}
