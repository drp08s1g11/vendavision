<?php

namespace App\Filament\Resources;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Filament\Exports\SaleExporter;
use App\Filament\Imports\SaleImporter;
use App\Filament\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?string $modelLabel = 'Venda';
    protected static ?string $pluralModelLabel = 'Vendas';
    protected static ?string $recordTitleAttribute = 'order_code';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make('Pedido')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order_code')
                            ->label('Código do pedido')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'AD-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4))),
                        Forms\Components\DatePicker::make('sold_at')
                            ->label('Data da venda')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->default(now()),
                        Forms\Components\Select::make('channel')
                            ->label('Canal / Marketplace')
                            ->options(SaleChannel::class)
                            ->required()
                            ->live()
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(SaleStatus::class)
                            ->default(SaleStatus::Pending)
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Itens da venda')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->columns(12)
                            ->minItems(1)
                            ->required()
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->addActionLabel('Adicionar item')
                            ->live()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produto')
                                    ->options(fn () => Product::query()
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Product $p) => [$p->id => "{$p->sku} — {$p->name}"]))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function (?string $state, Set $set) {
                                        $product = Product::find($state);
                                        $set('unit_price', $product?->sale_price);
                                        $set('unit_cost', $product?->cost_price);
                                    })
                                    ->columnSpan(5),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qtd')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 400)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Preço un.')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live(debounce: 400)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('unit_cost')
                                    ->label('Custo un.')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live(debounce: 400)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('item_fee')
                                    ->label('Taxa item')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(debounce: 400)
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]),

            Forms\Components\Group::make()->columnSpan(1)->schema([
                Forms\Components\Section::make('Taxas e frete')
                    ->schema([
                        Forms\Components\TextInput::make('marketplace_fee')
                            ->label('Comissão do marketplace')
                            ->prefix('R$')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 400)
                            ->hint(fn (Get $get) => ($c = self::channel($get('channel')))
                                ? "Sugestão {$c->getLabel()}: {$c->defaultFeePercent()}%" : null)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('applyFee')
                                    ->icon('heroicon-o-calculator')
                                    ->tooltip('Aplicar % padrão do canal sobre o faturamento')
                                    ->action(function (Get $get, Set $set) {
                                        $c = self::channel($get('channel'));
                                        $t = Sale::computeTotals(array_values($get('items') ?? []));
                                        $set('marketplace_fee', $c ? round($t['total_amount'] * $c->defaultFeePercent() / 100, 2) : 0);
                                    })
                            ),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Frete (custo do vendedor)')
                            ->prefix('R$')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 400),
                    ]),

                Forms\Components\Section::make('Resumo financeiro')
                    ->schema([
                        Forms\Components\Placeholder::make('summary')
                            ->hiddenLabel()
                            ->content(fn (Get $get) => self::renderSummary(Sale::computeTotals(
                                array_values($get('items') ?? []),
                                (float) $get('marketplace_fee'),
                                (float) $get('shipping_cost'),
                            ))),
                    ]),

                Forms\Components\Section::make('Observações')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->hiddenLabel()
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('LGPD: não registre nome, CPF, telefone ou endereço do comprador.'),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar Vendas (Excel / CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->exporter(SaleExporter::class),

                ImportAction::make()
                    ->label('Importar Vendas (Excel / CSV)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->importer(SaleImporter::class),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('order_code')->label('Pedido')
                    ->searchable()->sortable()->fontFamily('mono')->copyable(),
                Tables\Columns\TextColumn::make('sold_at')->label('Data')
                    ->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('channel')->label('Canal')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('items_count')->label('Itens')
                    ->counts('items')->alignCenter(),
                Tables\Columns\TextColumn::make('total_amount')->label('Faturamento')
                    ->money('BRL')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('BRL')->label('Total')),
                Tables\Columns\TextColumn::make('total_cost')->label('Custo')
                    ->money('BRL')->sortable()->toggleable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('BRL')->label('Total')),
                Tables\Columns\TextColumn::make('gross_profit')->label('Lucro')
                    ->money('BRL')->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('BRL')->label('Total')),
                Tables\Columns\TextColumn::make('profit_margin')->label('Margem')
                    ->badge()->suffix('%')->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 30 => 'success',
                        $state >= 10 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('sold_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('channel')->label('Canal')
                    ->options(SaleChannel::class)->multiple(),
                Tables\Filters\SelectFilter::make('status')->label('Status')
                    ->options(SaleStatus::class)->multiple(),
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('De')->native(false),
                        Forms\Components\DatePicker::make('to')->label('Até')->native(false),
                    ])
                    ->query(function (Builder $q, array $data): Builder {
                        return $q
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('sold_at', '>=', $date)
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('sold_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? 'De ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y') : null,
                        ($data['to'] ?? null) ? 'Até ' . \Carbon\Carbon::parse($data['to'])->format('d/m/Y') : null,
                    ])),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('markPaid')
                        ->label('Marcar como pago')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (Sale $r) => $r->status === SaleStatus::Pending)
                        ->action(fn (Sale $r) => $r->update(['status' => SaleStatus::Paid])),
                    Tables\Actions\Action::make('markShipped')
                        ->label('Marcar como enviado')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->visible(fn (Sale $r) => $r->status === SaleStatus::Paid)
                        ->action(fn (Sale $r) => $r->update(['status' => SaleStatus::Shipped])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar Selecionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(SaleExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    /* ---------- Helpers ---------- */

    private static function channel(mixed $value): ?SaleChannel
    {
        return $value instanceof SaleChannel ? $value : SaleChannel::tryFrom((string) $value);
    }

    private static function renderSummary(array $t): HtmlString
    {
        $brl = fn (float $v) => 'R$ ' . number_format($v, 2, ',', '.');
        $profitColor = $t['gross_profit'] < 0 ? '#dc2626' : '#16a34a';

        return new HtmlString(<<<HTML
            <dl style="display:grid;grid-template-columns:1fr auto;gap:.5rem 1rem;font-size:.9rem">
                <dt>Faturamento</dt><dd style="text-align:right;font-weight:600">{$brl($t['total_amount'])}</dd>
                <dt>Custo total</dt><dd style="text-align:right">{$brl($t['total_cost'])}</dd>
                <dt style="border-top:1px solid #e5e7eb;padding-top:.5rem">Lucro bruto</dt>
                <dd style="text-align:right;font-weight:700;color:{$profitColor};border-top:1px solid #e5e7eb;padding-top:.5rem">{$brl($t['gross_profit'])}</dd>
                <dt>Margem real</dt><dd style="text-align:right;font-weight:700;color:{$profitColor}">{$t['profit_margin']}%</dd>
            </dl>
        HTML);
    }
}
