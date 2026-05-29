<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Services\ComparisonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Front controller for the product comparison page + toggle endpoints.
 *
 * Routes are registered by ModuleDiscovery::bootModuleResources() from
 * modules/comparison/routes/web.php when the module is enabled.
 */
class ComparisonController extends Controller
{
    public function __construct(private readonly ComparisonService $comparison) {}

    /** GET /comparison — render the comparison table. */
    public function index(): View
    {
        $data = $this->comparison->getComparisonData();

        return view('comparison::index', [
            'products' => $data['products'],
            'attributes' => $data['attributes'],
        ]);
    }

    /** POST /comparison/add — add a product to the comparison set. */
    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $added = $this->comparison->add($productId);

        if ($request->expectsJson()) {
            return response()->json([
                'added' => $added,
                'count' => $this->comparison->getCount(),
            ]);
        }

        return back();
    }

    /** POST /comparison/remove — remove a product from the comparison set. */
    public function remove(Request $request): RedirectResponse|JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $this->comparison->remove($productId);

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $this->comparison->getCount(),
            ]);
        }

        return back();
    }

    /** POST /comparison/clear — empty the comparison set. */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->comparison->clear();

        if ($request->expectsJson()) {
            return response()->json(['count' => 0]);
        }

        return redirect()->route('gazu.comparison');
    }
}
