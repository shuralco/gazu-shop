# Module Hooks — Extension Points Registry

Hooks are the **documented surface** that modules use to react to core
events or modify core values without editing core code. They're a thin
WordPress-style facade over Laravel's Event system.

## Two flavours

### Actions — fire-and-forget

```php
use App\Support\Hooks;

// Core code fires:
Hooks::do('order.paid', $order);

// Module listens:
Hooks::on('order.paid', function (Order $order) {
    LoyaltyService::awardPoints($order);
});
```

Return values from listeners are ignored. Order is not guaranteed unless you
explicitly set listener priority (Laravel doesn't natively support priority,
so don't rely on registration order across modules).

### Filters — value transformation

```php
use App\Support\Hooks;

// Core code asks for a (possibly modified) value:
$price = Hooks::filter('product.price', $product->base_price, $product, $user);

// Module modifies:
Hooks::addFilter('product.price', function (float $price, Product $p, ?User $u) {
    if ($p->is_on_sale) {
        return $price * 0.9;
    }
    return $price;  // Always return — even if unchanged
});
```

Each listener receives the **current** value and returns the (possibly modified)
value. Filters chain: listener A's output is listener B's input. Always return
the value, even if you don't change it.

## How to listen in a module

In your module's `ServiceProvider::boot()`:

```php
// modules/loyalty/src/Providers/LoyaltyServiceProvider.php
use App\Support\Hooks;

public function boot(): void
{
    Hooks::on('order.paid', [LoyaltyService::class, 'awardPoints']);

    Hooks::addFilter('product.price', function (float $price, Product $p, ?User $u) {
        return $u ? LoyaltyService::applyTierDiscount($price, $u) : $price;
    });
}
```

Register the provider in your `module.json`:

```json
{
  "providers": [
    "Modules\\Loyalty\\Providers\\LoyaltyServiceProvider"
  ]
}
```

When the module is OFF, the provider doesn't load → listeners aren't registered → core proceeds unchanged.

## Official action hooks

> **Status legend:** ✅ shipped · 🚧 planned (proposal — not yet emitted)

### Orders

| Hook | Payload | Status | Description |
|---|---|---|---|
| `order.created` | `Order` | 🚧 | Order row inserted (before payment) |
| `order.paid` | `Order` | 🚧 | Payment confirmed, status → paid |
| `order.status_changed` | `Order $order, string $from, string $to` | 🚧 | Any status transition |
| `order.shipped` | `Order $order, NpShipment|UpShipment` | 🚧 | TTN created |
| `order.cancelled` | `Order, ?string $reason` | 🚧 | |
| `order.delivered` | `Order` | 🚧 | Recipient confirmed delivery |

### Products

| Hook | Payload | Status |
|---|---|---|
| `product.saved` | `Product` | 🚧 |
| `product.deleted` | `Product` | 🚧 |
| `product.stock_changed` | `Product, int $delta` | 🚧 |
| `product.viewed` | `Product, ?User` | 🚧 |

### Users

| Hook | Payload | Status |
|---|---|---|
| `user.registered` | `User` | 🚧 |
| `user.logged_in` | `User` | 🚧 |
| `user.email_verified` | `User` | 🚧 |
| `user.password_reset` | `User` | 🚧 |

### Cart & Checkout

| Hook | Payload | Status |
|---|---|---|
| `cart.updated` | `Cart` | 🚧 |
| `cart.product_added` | `Cart, Product, int $qty` | 🚧 |
| `checkout.rendering` | `Cart, ?User` | 🚧 |
| `checkout.submitted` | `Order` | 🚧 |

### Module / Theme lifecycle

| Hook | Payload | Status |
|---|---|---|
| `module.enabled` | `string $name` | ✅ via ModuleObserver |
| `module.disabled` | `string $name` | ✅ via ModuleObserver |
| `theme.activated` | `string $name` | 🚧 |

## Official filter hooks

| Filter | Value type | Context | Status |
|---|---|---|---|
| `product.price` | `float` | `Product, ?User` | 🚧 |
| `product.title` | `string` | `Product` | 🚧 |
| `cart.shipping_cost` | `float` | `Cart, ShippingMethod` | 🚧 |
| `cart.discount` | `float` | `Cart` | 🚧 |
| `order.total` | `float` | `Order` | 🚧 |
| `menu.items` | `array` | `string $location, ?User` | 🚧 |
| `homepage.blocks` | `Collection<Block>` | — | 🚧 |
| `seo.meta_title` | `string` | `string $route, Model? $model` | 🚧 |
| `seo.meta_description` | `string` | `string $route, Model? $model` | 🚧 |
| `email.recipient` | `array` | `string $template, mixed $context` | 🚧 |

## Implementation status

The `Hooks::do()` and `Hooks::filter()` API is shipped (in `app/Support/Hooks.php`).
The list above represents the **intended extension surface**. Most hooks are not
yet emitted by core — they will be added as the engine matures and modules need them.

When you want to use a 🚧 hook:

1. Check that core actually emits it (`grep -r "Hooks::do.*'order.paid'" app/`)
2. If not, add the `Hooks::do(...)` call where it logically belongs in core
3. Update this doc to ✅
4. Use the hook in your module

## Adding a new hook

Two questions before adding a hook:

1. **Is it action or filter?** — Does any module need to modify the value (filter)
   or just react (action)?
2. **What's the natural place to emit it?** — Usually the service or controller
   where the operation completes. Avoid emitting from views.

Then:

```php
// In core (e.g. app/Services/OrderService.php)
use App\Support\Hooks;

public function markPaid(Order $order): void
{
    $order->update(['payment_status' => 'paid']);
    Hooks::do('order.paid', $order);
}
```

And add to this doc with status ✅.

## Naming conventions

- **Lower-case dotted names**: `order.paid`, NOT `OrderPaid` or `ORDER_PAID`
- **Subject.verb format**: subject first (`order.paid`), past tense for actions
  (`order.created` not `order.create`)
- **Filter names are nouns**: `product.price` (what's being filtered), not
  `filter_product_price`
- **Module-private hooks** can use `modulename.event` prefix:
  `loyalty.points_awarded`, `wholesale.tier_upgraded`

## Anti-patterns

- ❌ Emitting hooks from blade views — emit from controllers/services
- ❌ Mutating payload objects in actions — actions are observers; use filter if you need to change something
- ❌ Hooks that take huge collections you'd be tempted to lazy-load —
  pass IDs or use a real DB query inside the listener
- ❌ Long-running work in synchronous listeners — dispatch a queued job
- ❌ Module-to-module direct calls — listen for the other module's hook instead

## When NOT to use hooks

Use direct method calls or Service classes when:

- The operation is **always** part of the flow (not optional)
- You need a synchronous return value not handled by `filter`
- The dependency belongs to core anyway (Stripe API, email sender)

Use hooks when:

- The behavior is **optional** (a module might or might not be enabled)
- Multiple modules might want to react
- You want to keep core agnostic of which modules exist

## Examples by module

### Loyalty awarding points on paid order

```php
// modules/loyalty/src/Providers/LoyaltyServiceProvider.php
Hooks::on('order.paid', function (Order $order) {
    $points = floor($order->total / 10);
    $order->user->loyaltyTransactions()->create([
        'type' => 'earn',
        'points' => $points,
        'reason' => "Order #{$order->id}",
    ]);
});
```

### Wholesale price filter for B2B customers

```php
// modules/wholesale/src/Providers/WholesaleServiceProvider.php
Hooks::addFilter('product.price', function (float $price, Product $p, ?User $u) {
    if (! $u?->customer_group_id) return $price;
    $groupPrice = ProductGroupPrice::where('product_id', $p->id)
        ->where('customer_group_id', $u->customer_group_id)
        ->value('price');
    return $groupPrice ?: $price;
});
```

### Loyalty tier discount on checkout total

```php
// modules/loyalty/src/Providers/LoyaltyServiceProvider.php
Hooks::addFilter('cart.discount', function (float $current, Cart $cart) {
    if (! auth()->check()) return $current;
    $tierDiscount = LoyaltyService::currentTierDiscount(auth()->user(), $cart->total);
    return $current + $tierDiscount;
});
```

## Reference

- `app/Support/Hooks.php` — implementation
- Laravel docs on Events — https://laravel.com/docs/events
