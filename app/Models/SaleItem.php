<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'item_name', 'quantity', 'unit_price', 'unit_cost',
        'subtotal', 'cost_subtotal', 'item_fee',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cost_subtotal' => 'decimal:2',
        'item_fee' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (SaleItem $item) {
            // Snapshot automático caso não informado
            if ($item->product && $item->unit_price === null) {
                $item->unit_price = $item->product->sale_price;
            }
            if ($item->product && $item->unit_cost === null) {
                $item->unit_cost = $item->product->cost_price;
            }

            $item->subtotal = round($item->quantity * (float) $item->unit_price, 2);
            $item->cost_subtotal = round($item->quantity * (float) $item->unit_cost, 2);
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    protected function profit(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->subtotal - (float) $this->cost_subtotal - (float) $this->item_fee, 2
        ));
    }
}
