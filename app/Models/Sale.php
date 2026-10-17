<?php

namespace App\Models;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code', 'sold_at', 'channel', 'status',
        'marketplace_fee', 'shipping_cost',
        'total_amount', 'total_cost', 'gross_profit', 'profit_margin', 'notes',
    ];

    protected $casts = [
        'sold_at' => 'date',
        'channel' => SaleChannel::class,
        'status' => SaleStatus::class,
        'marketplace_fee' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
    ];

    /* ---------- Relacionamentos ---------- */

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /* ---------- Regras de negócio ---------- */

    /**
     * Cálculo puro (sem banco) – reutilizado pelo formulário Filament para preview em tempo real.
     *
     * @param  array<int, array{quantity:int|float, unit_price:float, unit_cost:float, item_fee?:float}>  $items
     * @return array{total_amount:float,total_cost:float,gross_profit:float,profit_margin:float}
     */
    public static function computeTotals(array $items, float $marketplaceFee = 0, float $shipping = 0): array
    {
        $revenue = 0.0;
        $cost = 0.0;

        foreach ($items as $i) {
            $qty = (float) ($i['quantity'] ?? 0);
            $revenue += $qty * (float) ($i['unit_price'] ?? 0);
            $cost += $qty * (float) ($i['unit_cost'] ?? 0) + (float) ($i['item_fee'] ?? 0);
        }

        $cost += $marketplaceFee + $shipping;
        $profit = $revenue - $cost;

        return [
            'total_amount' => round($revenue, 2),
            'total_cost' => round($cost, 2),
            'gross_profit' => round($profit, 2),
            'profit_margin' => $revenue > 0 ? round($profit / $revenue * 100, 2) : 0.0,
        ];
    }

    /** Recalcula e persiste os totais a partir dos itens salvos. */
    public function recalculateTotals(): self
    {
        $totals = self::computeTotals(
            $this->items()->get(['quantity', 'unit_price', 'unit_cost', 'item_fee'])->toArray(),
            (float) $this->marketplace_fee,
            (float) $this->shipping_cost,
        );

        $this->forceFill($totals)->saveQuietly();

        return $this;
    }

    /* ---------- Scopes ---------- */

    /** Apenas pedidos que contam como faturamento (Pago/Enviado). */
    public function scopeRevenue(Builder $q): Builder
    {
        return $q->whereIn('status', SaleStatus::revenue());
    }

    public function scopeBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        return $q->when($from, fn ($q) => $q->whereDate('sold_at', '>=', $from))
                 ->when($to, fn ($q) => $q->whereDate('sold_at', '<=', $to));
    }
}
