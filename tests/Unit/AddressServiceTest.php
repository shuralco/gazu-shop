<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserAddress;
use App\Services\AddressService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private AddressService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AddressService::class);
    }

    public function test_create_address(): void
    {
        $user = User::factory()->create();

        $address = $this->service->create($user, [
            'label' => 'Дім',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
        ]);

        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'user_id' => $user->id,
            'city' => 'Київ',
        ]);
    }

    public function test_first_address_becomes_default(): void
    {
        $user = User::factory()->create();

        $address = $this->service->create($user, [
            'label' => 'Дім',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
        ]);

        $this->assertTrue($address->is_default);
    }

    public function test_set_default(): void
    {
        $user = User::factory()->create();

        $addr1 = $this->service->create($user, [
            'label' => 'Дім',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
        ]);

        $addr2 = $this->service->create($user, [
            'label' => 'Робота',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Львів',
            'address' => 'пл. Ринок, 1',
        ]);

        $this->service->setDefault($user, $addr2->id);

        $this->assertFalse($addr1->fresh()->is_default);
        $this->assertTrue($addr2->fresh()->is_default);
    }

    public function test_delete_reassigns_default(): void
    {
        $user = User::factory()->create();

        $addr1 = $this->service->create($user, [
            'label' => 'Дім',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
        ]);

        $addr2 = $this->service->create($user, [
            'label' => 'Робота',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Львів',
            'address' => 'пл. Ринок, 1',
        ]);

        // addr1 is default (first created), delete it
        $this->service->delete($addr1);

        $this->assertDatabaseMissing('user_addresses', ['id' => $addr1->id]);
        $this->assertTrue($addr2->fresh()->is_default);
    }

    public function test_update_address(): void
    {
        $user = User::factory()->create();

        $address = $this->service->create($user, [
            'label' => 'Дім',
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380501234567',
            'city' => 'Київ',
            'address' => 'вул. Хрещатик, 1',
        ]);

        $updated = $this->service->update($address, [
            'city' => 'Одеса',
            'address' => 'Дерибасівська, 10',
        ]);

        $this->assertEquals('Одеса', $updated->city);
        $this->assertEquals('Дерибасівська, 10', $updated->address);
    }
}
