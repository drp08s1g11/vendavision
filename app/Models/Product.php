<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
         'name', 'sku', 'category', 'description', 'cost_price', 'sale_price', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['unit_profit', 'estimated_margin'];

    /* ---------- Relacionamentos ---------- */

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /* ---------- Atributos calculados ---------- */

    protected function unitProfit(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->sale_price - (float) $this->cost_price, 2));
    }

    /** Margem estimada (%) sobre o preço de venda. */
    protected function estimatedMargin(): Attribute
    {
        return Attribute::get(fn () => self::calculateMargin((float) $this->sale_price, (float) $this->cost_price));
    }

    public static function calculateMargin(float $price, float $cost): float
    {
        return $price > 0 ? round((($price - $cost) / $price) * 100, 2) : 0.0;
    }

    /* ---------- Scopes ---------- */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /**
     * Agrega métricas de vendas (somente pedidos Pagos/Enviados) por produto.
     * Uso: Product::withSalesMetrics($from, $to)->orderByDesc('units_sold')->limit(10)->get()
     */
    public function scopeWithSalesMetrics(Builder $q, ?string $from = null, ?string $to = null): Builder
    {
        return $q->select('products.*')
            ->selectRaw('COALESCE(SUM(si.quantity),0) AS units_sold')
            ->selectRaw('COALESCE(SUM(si.subtotal),0) AS revenue')
            ->selectRaw('COALESCE(SUM(si.subtotal - si.cost_subtotal - si.item_fee),0) AS profit')
            ->selectRaw('CASE WHEN SUM(si.subtotal) > 0
                THEN ROUND(SUM(si.subtotal - si.cost_subtotal - si.item_fee) / SUM(si.subtotal) * 100, 2)
                ELSE 0 END AS real_margin')
            ->join('sale_items as si', 'si.product_id', '=', 'products.id')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->whereIn('s.status', SaleStatus::revenue())
            ->when($from, fn ($q) => $q->whereDate('s.sold_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('s.sold_at', '<=', $to))
            ->groupBy('products.id');
    }
}
