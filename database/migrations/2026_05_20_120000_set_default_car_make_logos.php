<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seed default monogram logos for the known car makes. Idempotent — only fills
 * makes that don't already have a logo_path, so an admin upload is never
 * overwritten. SVG files ship in public/img/car-makes/.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('car_makes') || ! Schema::hasColumn('car_makes', 'logo_path')) {
            return;
        }

        $logos = [
            'byd'        => '/img/car-makes/byd.svg',
            'chery'      => '/img/car-makes/chery.svg',
            'geely'      => '/img/car-makes/geely.svg',
            'haval'      => '/img/car-makes/haval.svg',
            'great-wall' => '/img/car-makes/great-wall.svg',
            'jac'        => '/img/car-makes/jac.svg',
            'mg'         => '/img/car-makes/mg.svg',
            'vw'         => '/img/car-makes/vw.svg',
        ];

        foreach ($logos as $slug => $path) {
            DB::table('car_makes')
                ->where('slug', $slug)
                ->where(fn ($q) => $q->whereNull('logo_path')->orWhere('logo_path', ''))
                ->update(['logo_path' => $path]);
        }
    }

    public function down(): void
    {
        // Non-destructive: leave logos in place on rollback.
    }
};
