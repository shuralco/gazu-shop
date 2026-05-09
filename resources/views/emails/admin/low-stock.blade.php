<x-mail::message>
# ⚠️ Товари закінчуються

На складі залишилось мало одиниць ({{ $products->count() }} товарів).

<x-mail::table>
| SKU | Назва | Залишок | Поріг |
|:----|:------|:--------|:------|
@foreach($products as $product)
| {{ $product->sku ?? '—' }} | {{ \Illuminate\Support\Str::limit($product->getTranslation('title', 'uk', false) ?? $product->name ?? '', 40) }} | {{ $product->quantity }} | {{ $product->min_quantity ?? 5 }} |
@endforeach
</x-mail::table>

<x-mail::button :url="url('/admin/products')">
Перейти до товарів
</x-mail::button>

З повагою,<br>
{{ \App\Models\DisplaySetting::get('site_name', 'SimpleShop') }} (auto-alert)
</x-mail::message>
