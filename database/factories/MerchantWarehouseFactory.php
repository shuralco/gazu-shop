<?php

namespace Database\Factories;

use App\Models\MerchantWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantWarehouseFactory extends Factory
{
    protected $model = MerchantWarehouse::class;

    public function definition(): array
    {
        $code = strtoupper($this->faker->unique()->bothify('TEST-##'));

        return [
            'code' => $code,
            'name' => 'Тест склад '.$code,
            'type' => MerchantWarehouse::TYPE_OWN,
            'country' => 'UA',
            'city' => $this->faker->randomElement(['Київ', 'Львів', 'Одеса', 'Харків']),
            'address' => $this->faker->streetAddress(),
            'is_active' => true,
            'is_default' => false,
            'pickup_supported' => false,
            'sort_order' => 0,
        ];
    }

    public function default(): self
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function withNpSender(): self
    {
        return $this->state(fn () => [
            'np_sender_ref' => $this->faker->uuid(),
            'np_sender_city_ref' => $this->faker->uuid(),
            'np_sender_warehouse_ref' => $this->faker->uuid(),
            'np_contact_person_ref' => $this->faker->uuid(),
            'np_sender_phone' => '+380'.$this->faker->numerify('#########'),
        ]);
    }
}
