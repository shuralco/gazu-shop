<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\HomeComponent::class)->name('home');
Route::get('/category/{slug}', \App\Livewire\Product\CategoryComponent::class)->name('category');
Route::get('/product/{slug}', \App\Livewire\Product\ProductComponent::class)->name('product');
Route::get('/cart', \App\Livewire\Cart\Cart::class)->name('cart');
Route::get('/checkout', \App\Livewire\Cart\CheckoutComponent::class)->name('checkout');
Route::get('/search', \App\Livewire\Search\SearchComponent::class)->name('search');

Route::middleware('guest')->group(function () {
    Route::get('/register', \App\Livewire\User\RegisterComponent::class)->name('register');
    Route::get('/login', \App\Livewire\User\LoginComponent::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();

        return redirect()->route('login');
    })->name('logout');
    Route::get('/account', \App\Livewire\User\AccountComponent::class)->name('account');
    Route::get('/change-account', \App\Livewire\User\ChangeAccountComponent::class)->name('change-account');
    Route::get('/orders', \App\Livewire\User\OrderComponent::class)->name('orders');
    Route::get('/order-show/{id}', \App\Livewire\User\OrderShowComponent::class)->name('orders-show');
});
