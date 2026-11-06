<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $pluralModelLabel = 'Produtos';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Section::make('Identificação do Produto')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome do Produto')
                                    ->placeholder('Ex: Quadro Canvas Minimalista 80x120cm')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU / Código de Referência')
                                    ->placeholder('Ex: AMB-QD-001')
                                    ->required()
                                    ->maxLength(64)
                                    ->unique(
                                        table: 'products',
                                        column: 'sku',
                                        ignoreRecord: true
                                    )
                                    ->validationMessages([
                                        'unique' => 'Este SKU já está cadastrado (verifique também se não está na lixeira).',
                                    ]),

                                Forms\Components\TextInput::make('category')
                                    ->label('Categoria do Item')
                                    ->placeholder('Ex: Quadros, Espelhos, Almofadas')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('stock_reference')
                                    ->label('Fornecedor Parceiro')
                                    ->placeholder('Ex: Arte Quadros SP')
                                    ->maxLength(255),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Produto Ativo para Vendas')
                                    ->helperText('Se desmarcado, não aparecerá na seleção de novos pedidos.')
                                    ->default(true)
                                    ->inline(false),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição / Ficha Técnica')
                                    ->placeholder('Detalhes de acabamento, materiais e fornecedor parceiro...')
                                    ->rows(3)
                                    ->maxLength(2000)
                                    ->columnSpan(2),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Section::make('Engenharia de Preços')
                            ->schema([
                                Forms\Components\TextInput::make('cost_price')
                                    ->label('Custo do Fornecedor')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(999999.99)
                                    ->required()
                                    ->live(debounce: 300)
                                    ->helperText('Custo cobrado pelo fornecedor de dropshipping.'),

                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Preço de Venda Praticado')
                                    ->prefix('R$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(999999.99)
                                    ->required()
                                    ->live(debounce: 300)
                                    ->helperText('Preço anunciado na loja ou marketplace.'),

                                Forms\Components\Placeholder::make('margin_preview')
                                    ->label('Lucro Bruto & Margem')
                                    ->content(function (Get $get) {
                                        $cost = (float) $get('cost_price');
                                        $sale = (float) $get('sale_price');

                                        if ($sale <= 0) {
                                            return 'Informe o preço de venda para simular.';
                                        }

                                        $profit = $sale - $cost;
                                        $margin = ($profit / $sale) * 100;
                                        $color = $profit >= 0 ? 'text-green-600' : 'text-red-600';

                                        $profitFormatted = 'R$ ' . number_format($profit, 2, ',', '.');
                                        $marginFormatted = number_format($margin, 1, ',', '.') . '%';

                                        return new \Illuminate\Support\HtmlString(
                                            "<span class='font-bold {$color}'>{$profitFormatted} ({$marginFormatted})</span>"
                                        );
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar Produtos (.CSV / Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->exporter(ProductExporter::class),

                ImportAction::make()
                    ->label('Importar Produtos (.CSV)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->importer(ProductImporter::class),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Geral'),

                Tables\Columns\TextColumn::make('stock_reference')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Custo')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço Venda')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('unit_profit')
                    ->label('Lucro Estimado')
                    ->money('BRL')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => 
                        $query->orderByRaw('(sale_price - cost_price) ' . $direction)
                    )
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('estimated_margin')
                    ->label('Margem %')
                    ->suffix('%')
                    ->badge()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => 
                        $query->orderByRaw('CASE WHEN sale_price > 0 THEN ((sale_price - cost_price) / sale_price) ELSE 0 END ' . $direction)
                    )
                    ->color(fn (float $state): string => match (true) {
                        $state >= 35 => 'success',
                        $state >= 20 => 'info',
                        $state > 0 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Disponibilidade')
                    ->placeholder('Todos os produtos')
                    ->trueLabel('Apenas Ativos (em catálogo)')
                    ->falseLabel('Apenas Inativos (fora de linha)'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Lixeira'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->tooltip('Restaurar produto da lixeira'),
                // ForceDelete removido por segurança para proteger integridade referencial com vendas
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar Selecionados')
                        ->exporter(ProductExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    // ForceDeleteBulk removido por segurança
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
