<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\FilterGroupPolicy;
use App\Policies\FilterPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Мапування політик для моделей застосунку.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Order::class => OrderPolicy::class,
        FilterGroup::class => FilterGroupPolicy::class,
        Filter::class => FilterPolicy::class,
    ];

    /**
     * Реєстрація будь-яких сервісів аутентифікації / авторизації.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Додатковий Gate для перевірки адміністративних прав
        Gate::define('admin-access', function (User $user) {
            return $user->is_admin;
        });

        // Gate для перевірки власності ресурсу
        Gate::define('owns-resource', function (User $user, $resource) {
            return $user->id === $resource->user_id;
        });

        // Gate для доступу до публічного контенту
        Gate::define('view-public-content', function (?User $user = null) {
            return true; // Публічний контент доступний всім
        });
    }
}
