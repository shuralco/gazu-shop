<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\FilterLanding;
use App\Support\ProductCardDecorator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterLandingController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $landing = FilterLanding::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        DB::table('filter_landings')->where('id', $landing->id)->increment('views_count');

        $perPage = (int) ($request->input('per_page', 24));
        $paginator = $landing->productsQuery()
            ->with(['category', 'brand', 'inventory.warehouse'])
            ->withCount('approvedReviews as reviews_count')
            ->orderByRaw('CASE WHEN image IS NOT NULL AND image != "" THEN 0 ELSE 1 END')
            ->orderBy('is_hit', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // Decorate so product-card blade gets scalar fields (not raw relations).
        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($p) => ProductCardDecorator::decorate($p))
        );

        return view('gazu.landing.show', [
            'landing' => $landing,
            'products' => $paginator,
            'appliedFilters' => $landing->filters(),
        ]);
    }
}
