<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 Top 5: Maior Contribuição de Lucro Real (R$)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as units_sold')
                    ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.unit_price), 0) as total_revenue')
                    ->selectRaw('COALESCE(SUM(sale_items.quantity * (sale_items.unit_price - sale_items.unit_cost - sale_items.item_fee)), 0) as total_profit')
                    ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
                    ->leftJoin('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->where(function ($q) {
                        $q->whereIn('sales.status', [SaleStatus::Paid->value, SaleStatus::Shipped->value])
                            ->orWhereNull('sales.id');
                    })
                    ->groupBy('products.id')
                    ->orderByDesc('total_profit')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->description(fn (Product $record) => 'SKU: ' . $record->sku)
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('units_sold')
                    ->label('Vendas')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Faturado')
                    ->money('BRL')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Lucro Real')
                    ->money('BRL')
                    ->color('success')
                    ->weight('bold')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
