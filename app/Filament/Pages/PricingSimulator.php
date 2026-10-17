<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class PricingSimulator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Simulador de Preço';

    protected static ?string $title = 'Simulador de Precificação & Margem (Dropshipping)';

    protected static ?string $navigationGroup = 'Gestão Comercial';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.pricing-simulator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'cost_price' => 80.00,
            'shipping_cost' => 15.00,
            'tax_rate' => 4.0,
            'target_margin' => 25.0,
            'commission_rate' => 16.0,
            'fixed_fee' => 6.00,
            'channel' => 'Mercado Livre (Premium)',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parâmetros de Custo e Margem')
                    ->description('Simule o preço de venda ideal para dropshipping considerando taxas de marketplaces e impostos.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('cost_price')
                                ->label('Custo de Aquisição (Fornecedor)')
                                ->prefix('R$')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(debounce: 400)
                                ->helperText('Preço cobrado pelo fabricante/parceiro.'),

                            TextInput::make('shipping_cost')
                                ->label('Frete / Embalagem')
                                ->prefix('R$')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(debounce: 400)
                                ->helperText('Custo de envio assumido pela loja.'),

                            TextInput::make('fixed_fee')
                                ->label('Taxa Fixa do Canal')
                                ->prefix('R$')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(debounce: 400)
                                ->helperText('Ex: taxa fixa por item no Mercado Livre/Shopee.'),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('channel')
                                ->label('Canal Predefinido')
                                ->options([
                                    'Mercado Livre (Premium)' => 'Mercado Livre Premium (16% + R$ 6)',
                                    'Mercado Livre (Clássico)' => 'Mercado Livre Clássico (11% + R$ 6)',
                                    'Shopee (Padrão)' => 'Shopee Padrão (14% + R$ 4)',
                                    'Shopee (Frete Grátis)' => 'Shopee Frete Grátis (20% + R$ 4)',
                                    'Amazon Brasil' => 'Amazon Brasil (15%)',
                                    'Loja Própria / WhatsApp' => 'Loja Própria (Cartão/Gateway 4%)',
                                    'Personalizado' => 'Personalizado / Outro',
                                ])
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    match ($state) {
                                        'Mercado Livre (Premium)' => [$set('commission_rate', 16.0), $set('fixed_fee', 6.0)],
                                        'Mercado Livre (Clássico)' => [$set('commission_rate', 11.0), $set('fixed_fee', 6.0)],
                                        'Shopee (Padrão)' => [$set('commission_rate', 14.0), $set('fixed_fee', 4.0)],
                                        'Shopee (Frete Grátis)' => [$set('commission_rate', 20.0), $set('fixed_fee', 4.0)],
                                        'Amazon Brasil' => [$set('commission_rate', 15.0), $set('fixed_fee', 0.0)],
                                        'Loja Própria / WhatsApp' => [$set('commission_rate', 4.0), $set('fixed_fee', 0.0)],
                                        default => null,
                                    };
                                }),

                            TextInput::make('commission_rate')
                                ->label('Comissão do Marketplace (%)')
                                ->suffix('%')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(90)
                                ->required()
                                ->live(debounce: 400),

                            Grid::make(2)->schema([
                                TextInput::make('tax_rate')
                                    ->label('Impostos (Simples)')
                                    ->suffix('%')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(40)
                                    ->required()
                                    ->live(debounce: 400),

                                TextInput::make('target_margin')
                                    ->label('Margem Desejada')
                                    ->suffix('%')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(80)
                                    ->required()
                                    ->live(debounce: 400),
                            ]),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Calcula a precificação com base na fórmula:
     * Preço = (Custo + Frete + TaxaFixa) / (1 - (Comissão% + Imposto% + Margem%))
     */
    public function getCalculationResultsProperty(): array
    {
        $cost = (float) ($this->data['cost_price'] ?? 80.0);
        $shipping = (float) ($this->data['shipping_cost'] ?? 15.0);
        $fixedFee = (float) ($this->data['fixed_fee'] ?? 6.0);
        $commissionRate = ((float) ($this->data['commission_rate'] ?? 16.0)) / 100;
        $taxRate = ((float) ($this->data['tax_rate'] ?? 4.0)) / 100;
        $targetMarginRate = ((float) ($this->data['target_margin'] ?? 25.0)) / 100;

        $totalDirectCost = $cost + $shipping + $fixedFee;
        $deductionDenominator = 1 - ($commissionRate + $taxRate + $targetMarginRate);

        if ($deductionDenominator <= 0.05) {
            $deductionDenominator = 0.05; // Evita divisão por zero ou negativa
        }

        $suggestedPrice = $totalDirectCost / $deductionDenominator;
        $commissionAmount = $suggestedPrice * $commissionRate;
        $taxAmount = $suggestedPrice * $taxRate;
        $netProfit = $suggestedPrice - $cost - $shipping - $fixedFee - $commissionAmount - $taxAmount;
        $netMarginReal = $suggestedPrice > 0 ? ($netProfit / $suggestedPrice) * 100 : 0;

        // Ponto de equilíbrio (Break-even): Preço para lucro R$ 0,00
        $breakEvenDenominator = 1 - ($commissionRate + $taxRate);
        $breakEvenPrice = $breakEvenDenominator > 0 ? $totalDirectCost / $breakEvenDenominator : $totalDirectCost;

        // Markup Multiplicador sobre o custo do fornecedor
        $markup = $cost > 0 ? $suggestedPrice / $cost : 0;

        return [
            'suggested_price' => $suggestedPrice,
            'cost_price' => $cost,
            'shipping_cost' => $shipping,
            'fixed_fee' => $fixedFee,
            'commission_amount' => $commissionAmount,
            'tax_amount' => $taxAmount,
            'net_profit' => $netProfit,
            'net_margin' => $netMarginReal,
            'break_even_price' => $breakEvenPrice,
            'markup' => $markup,
        ];
    }

    /**
     * Tabela comparativa em tempo real com todos os principais canais de marketplace
     */
    public function getChannelsComparisonProperty(): array
    {
        $cost = (float) ($this->data['cost_price'] ?? 80.0);
        $shipping = (float) ($this->data['shipping_cost'] ?? 15.0);
        $taxRate = ((float) ($this->data['tax_rate'] ?? 4.0)) / 100;
        $targetMarginRate = ((float) ($this->data['target_margin'] ?? 25.0)) / 100;

        $presets = [
            [
                'name' => 'Mercado Livre (Premium)',
                'commission_rate' => 0.16,
                'fixed_fee' => 6.0,
                'badge' => 'primary',
            ],
            [
                'name' => 'Mercado Livre (Clássico)',
                'commission_rate' => 0.11,
                'fixed_fee' => 6.0,
                'badge' => 'info',
            ],
            [
                'name' => 'Shopee (Frete Grátis)',
                'commission_rate' => 0.20,
                'fixed_fee' => 4.0,
                'badge' => 'warning',
            ],
            [
                'name' => 'Shopee (Padrão)',
                'commission_rate' => 0.14,
                'fixed_fee' => 4.0,
                'badge' => 'warning',
            ],
            [
                'name' => 'Amazon Brasil',
                'commission_rate' => 0.15,
                'fixed_fee' => 0.0,
                'badge' => 'danger',
            ],
            [
                'name' => 'Loja Própria / WhatsApp',
                'commission_rate' => 0.04,
                'fixed_fee' => 0.0,
                'badge' => 'success',
            ],
        ];

        $results = [];

        foreach ($presets as $p) {
            $directCost = $cost + $shipping + $p['fixed_fee'];
            $denom = 1 - ($p['commission_rate'] + $taxRate + $targetMarginRate);
            if ($denom <= 0.05) $denom = 0.05;

            $price = $directCost / $denom;
            $commVal = $price * $p['commission_rate'];
            $taxVal = $price * $taxRate;
            $profit = $price - $cost - $shipping - $p['fixed_fee'] - $commVal - $taxVal;

            $results[] = [
                'name' => $p['name'],
                'commission_percent' => ($p['commission_rate'] * 100) . '%',
                'fixed_fee' => $p['fixed_fee'],
                'suggested_price' => $price,
                'commission_amount' => $commVal,
                'net_profit' => $profit,
                'badge' => $p['badge'],
            ];
        }

        return $results;
    }
}
