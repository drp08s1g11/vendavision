<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopVolumeProductsWidget extends BaseWidget
{
    protected static ?string $heading = '📦 Top 5: Maior Giro de Estoque (Unidades)';
    protected static ?int $sort = 5;
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
                    ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
                    ->leftJoin('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->where(function ($q) {
                        $q->whereIn('sales.status', [SaleStatus::Paid->value, SaleStatus::Shipped->value])
                            ->orWhereNull('sales.id');
                    })
                    ->groupBy('products.id')
                    ->orderByDesc('units_sold')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->description(fn (Product $record) => 'SKU: ' . $record->sku)
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('units_sold')
                    ->label('Giro (Unid.)')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço Unit.')
                    ->money('BRL')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Receita Total')
                    ->money('BRL')
                    ->weight('semibold')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
