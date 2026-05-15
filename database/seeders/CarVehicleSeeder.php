<?php

namespace Database\Seeders;

use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Database\Seeder;

class CarVehicleSeeder extends Seeder
{
    /**
     * Seed марки → моделі → двигуни for the car-selector widget.
     * Focused on the Ukrainian market: Chinese brands (GAZU specialization)
     * + VAG (most common second-hand market).
     *
     * Idempotent: uses firstOrCreate so re-running is safe.
     */
    public function run(): void
    {
        $tree = $this->tree();
        $makeSort = 0;
        foreach ($tree as $makeSlug => $makeData) {
            $make = CarMake::firstOrCreate(
                ['slug' => $makeSlug],
                ['name' => $makeData['name'], 'sort_order' => $makeSort++, 'is_active' => true]
            );

            $modelSort = 0;
            foreach ($makeData['models'] as $modelSlug => $modelData) {
                $model = CarModel::firstOrCreate(
                    ['make_id' => $make->id, 'slug' => $modelSlug],
                    [
                        'name' => $modelData['name'],
                        'body_type' => $modelData['body'] ?? null,
                        'years_range' => $modelData['years'] ?? null,
                        'sort_order' => $modelSort++,
                        'is_active' => true,
                    ]
                );

                $engineSort = 0;
                foreach ($modelData['engines'] as $code => $engineData) {
                    CarEngine::firstOrCreate(
                        ['model_id' => $model->id, 'code' => $code],
                        [
                            'label' => $engineData['label'] ?? $code,
                            'fuel_type' => $engineData['fuel'] ?? 'petrol',
                            'displacement' => $engineData['cc'] ?? null,
                            'hp' => $engineData['hp'] ?? null,
                            'years_range' => $engineData['years'] ?? null,
                            'sort_order' => $engineSort++,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /** @return array<string, array{name:string, models:array}> */
    private function tree(): array
    {
        return [
            'byd' => [
                'name' => 'BYD',
                'models' => [
                    'f3' => ['name' => 'F3', 'body' => 'sedan', 'years' => '2005-2014', 'engines' => [
                        '1.5'    => ['label' => '1.5 (BYD473QE)', 'cc' => 1.5, 'hp' => 109],
                        '1.6'    => ['label' => '1.6 (BYD483QB)', 'cc' => 1.6, 'hp' => 105],
                    ]],
                    'han' => ['name' => 'Han', 'body' => 'sedan', 'years' => '2020-', 'engines' => [
                        'ev'     => ['label' => 'EV (електро)', 'fuel' => 'electric', 'hp' => 517],
                        'dm-i'   => ['label' => 'DM-i 1.5T гібрид', 'fuel' => 'hybrid', 'cc' => 1.5, 'hp' => 218],
                    ]],
                    'song-plus' => ['name' => 'Song Plus', 'body' => 'suv', 'years' => '2020-', 'engines' => [
                        'dm-i'   => ['label' => 'DM-i 1.5T гібрид', 'fuel' => 'hybrid', 'cc' => 1.5, 'hp' => 197],
                    ]],
                    'seal'  => ['name' => 'Seal', 'body' => 'sedan', 'years' => '2022-', 'engines' => [
                        'ev'     => ['label' => 'EV', 'fuel' => 'electric', 'hp' => 530],
                    ]],
                    'tang'  => ['name' => 'Tang', 'body' => 'suv', 'years' => '2018-', 'engines' => [
                        '2.0t'   => ['label' => '2.0T бензин', 'cc' => 2.0, 'hp' => 208],
                        'ev'     => ['label' => 'EV', 'fuel' => 'electric', 'hp' => 489],
                    ]],
                ],
            ],
            'chery' => [
                'name' => 'Chery',
                'models' => [
                    'tiggo-4' => ['name' => 'Tiggo 4', 'body' => 'suv', 'years' => '2017-', 'engines' => [
                        '1.5'    => ['label' => '1.5 (SQR481FE)', 'cc' => 1.5, 'hp' => 106],
                        '1.5t'   => ['label' => '1.5T (SQRE4T15)', 'cc' => 1.5, 'hp' => 147],
                    ]],
                    'tiggo-7-pro' => ['name' => 'Tiggo 7 Pro', 'body' => 'suv', 'years' => '2020-', 'engines' => [
                        '1.5t'   => ['label' => '1.5T (SQRF4J15B)', 'cc' => 1.5, 'hp' => 147],
                        '2.0t'   => ['label' => '2.0T (SQRF4J20B)', 'cc' => 2.0, 'hp' => 197],
                    ]],
                    'tiggo-8-pro' => ['name' => 'Tiggo 8 Pro', 'body' => 'suv', 'years' => '2021-', 'engines' => [
                        '1.6t'   => ['label' => '1.6T (SQRF4J16)', 'cc' => 1.6, 'hp' => 184],
                        '2.0t'   => ['label' => '2.0T (SQRF4J20)', 'cc' => 2.0, 'hp' => 254],
                    ]],
                    'arrizo-5' => ['name' => 'Arrizo 5', 'body' => 'sedan', 'years' => '2016-', 'engines' => [
                        '1.5'    => ['label' => '1.5 (SQR481FE)', 'cc' => 1.5, 'hp' => 106],
                        '1.5t'   => ['label' => '1.5T (SQRE4T15)', 'cc' => 1.5, 'hp' => 147],
                    ]],
                ],
            ],
            'geely' => [
                'name' => 'Geely',
                'models' => [
                    'atlas' => ['name' => 'Atlas', 'body' => 'suv', 'years' => '2016-', 'engines' => [
                        '2.0'    => ['label' => '2.0 (JLY-4G20)', 'cc' => 2.0, 'hp' => 141],
                        '2.4'    => ['label' => '2.4 (JLY-4G24)', 'cc' => 2.4, 'hp' => 149],
                    ]],
                    'coolray' => ['name' => 'Coolray', 'body' => 'crossover', 'years' => '2018-', 'engines' => [
                        '1.5t'   => ['label' => '1.5T (JLH-3G15)', 'cc' => 1.5, 'hp' => 177],
                    ]],
                    'monjaro' => ['name' => 'Monjaro', 'body' => 'suv', 'years' => '2021-', 'engines' => [
                        '2.0t'   => ['label' => '2.0T (JLH-4G20)', 'cc' => 2.0, 'hp' => 238],
                    ]],
                    'tugella' => ['name' => 'Tugella', 'body' => 'suv', 'years' => '2019-', 'engines' => [
                        '2.0t'   => ['label' => '2.0T (JLH-4G20)', 'cc' => 2.0, 'hp' => 238],
                    ]],
                ],
            ],
            'haval' => [
                'name' => 'Haval',
                'models' => [
                    'h6' => ['name' => 'H6', 'body' => 'suv', 'years' => '2011-', 'engines' => [
                        '1.5t'   => ['label' => '1.5T (GW4G15B)', 'cc' => 1.5, 'hp' => 150],
                        '2.0t'   => ['label' => '2.0T (GW4C-20)', 'cc' => 2.0, 'hp' => 197],
                    ]],
                    'h9' => ['name' => 'H9', 'body' => 'suv', 'years' => '2014-', 'engines' => [
                        '2.0t'   => ['label' => '2.0T (GW4C-20)', 'cc' => 2.0, 'hp' => 218],
                    ]],
                    'jolion' => ['name' => 'Jolion', 'body' => 'crossover', 'years' => '2020-', 'engines' => [
                        '1.5t'   => ['label' => '1.5T (GW4B15)', 'cc' => 1.5, 'hp' => 150],
                    ]],
                    'dargo' => ['name' => 'Dargo', 'body' => 'suv', 'years' => '2020-', 'engines' => [
                        '2.0t'   => ['label' => '2.0T (GW4C-20)', 'cc' => 2.0, 'hp' => 211],
                    ]],
                ],
            ],
            'great-wall' => [
                'name' => 'Great Wall',
                'models' => [
                    'wingle-7' => ['name' => 'Wingle 7', 'body' => 'pickup', 'years' => '2018-', 'engines' => [
                        '2.0d'   => ['label' => '2.0D (GW4D20M)', 'fuel' => 'diesel', 'cc' => 2.0, 'hp' => 150],
                    ]],
                    'hover-h5' => ['name' => 'Hover H5', 'body' => 'suv', 'years' => '2010-2016', 'engines' => [
                        '2.4'    => ['label' => '2.4 бензин', 'cc' => 2.4, 'hp' => 122],
                        '2.0d'   => ['label' => '2.0 турбодизель', 'fuel' => 'diesel', 'cc' => 2.0, 'hp' => 150],
                    ]],
                ],
            ],
            'jac' => [
                'name' => 'JAC',
                'models' => [
                    's3' => ['name' => 'S3', 'body' => 'crossover', 'years' => '2014-', 'engines' => [
                        '1.5'    => ['label' => '1.5', 'cc' => 1.5, 'hp' => 111],
                        '1.6'    => ['label' => '1.6', 'cc' => 1.6, 'hp' => 126],
                    ]],
                    's5' => ['name' => 'S5', 'body' => 'suv', 'years' => '2013-', 'engines' => [
                        '2.0'    => ['label' => '2.0', 'cc' => 2.0, 'hp' => 134],
                        '2.0t'   => ['label' => '2.0T', 'cc' => 2.0, 'hp' => 190],
                    ]],
                ],
            ],
            'mg' => [
                'name' => 'MG',
                'models' => [
                    'zs'  => ['name' => 'ZS', 'body' => 'crossover', 'years' => '2017-', 'engines' => [
                        '1.5'    => ['label' => '1.5', 'cc' => 1.5, 'hp' => 113],
                        'ev'     => ['label' => 'EV', 'fuel' => 'electric', 'hp' => 142],
                    ]],
                    'hs'  => ['name' => 'HS', 'body' => 'suv', 'years' => '2018-', 'engines' => [
                        '1.5t'   => ['label' => '1.5T', 'cc' => 1.5, 'hp' => 169],
                        '2.0t'   => ['label' => '2.0T', 'cc' => 2.0, 'hp' => 224],
                    ]],
                ],
            ],
            'vw' => [
                'name' => 'Volkswagen',
                'models' => [
                    'golf-vii'  => ['name' => 'Golf VII', 'body' => 'hatchback', 'years' => '2012-2020', 'engines' => [
                        '1.4tsi' => ['label' => '1.4 TSI (CZCA/CHPA)', 'cc' => 1.4, 'hp' => 125],
                        '2.0tdi' => ['label' => '2.0 TDI (DCYA/DEJA)', 'fuel' => 'diesel', 'cc' => 2.0, 'hp' => 150],
                    ]],
                    'passat-b8' => ['name' => 'Passat B8', 'body' => 'sedan', 'years' => '2014-2022', 'engines' => [
                        '1.8tsi' => ['label' => '1.8 TSI (CJSA)', 'cc' => 1.8, 'hp' => 180],
                        '2.0tdi' => ['label' => '2.0 TDI (CRLB)', 'fuel' => 'diesel', 'cc' => 2.0, 'hp' => 150],
                    ]],
                    'tiguan-ii' => ['name' => 'Tiguan II', 'body' => 'suv', 'years' => '2016-', 'engines' => [
                        '1.4tsi' => ['label' => '1.4 TSI (CZDA)', 'cc' => 1.4, 'hp' => 150],
                        '2.0tsi' => ['label' => '2.0 TSI (CZPB)', 'cc' => 2.0, 'hp' => 220],
                    ]],
                ],
            ],
        ];
    }
}
