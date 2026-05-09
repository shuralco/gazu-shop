<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'warehouse_id', 'title', 'price', 'quantity', 'image', 'slug'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'warehouse_id');
    }

    public function getImageUrl(): string
    {
        if ($this->image) {
            $imagePath = str_starts_with($this->image, '/') ? $this->image : '/'.$this->image;

            return preg_replace('/\/+/', '/', $imagePath);
        }

        return '/assets/img/default-product.jpg';
    }
}
