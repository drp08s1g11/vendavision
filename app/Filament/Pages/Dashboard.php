<?php

namespace App\Filament\Pages;

use App\Enums\SaleChannel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtro de Desempenho Executivo')
                    ->description('Filtre vendas por período e marketplace para recalcular KPIs, gráficos e rankings em tempo real.')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Data Inicial')
                            ->placeholder('Ex: 01/01/2026')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),

                        DatePicker::make('endDate')
                            ->label('Data Final')
                            ->placeholder('Ex: 31/12/2026')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),

                        Select::make('channel')
                            ->label('Canal de Venda')
                            ->placeholder('Todos os Canais Ativos')
                            ->options(function () {
                                $options = [];
                                foreach (SaleChannel::cases() as $case) {
                                    $provider = match ($case) {
                                        SaleChannel::Shopee => 'shopee',
                                        SaleChannel::MercadoLivre => 'mercadolivre',
                                        SaleChannel::Amazon => 'amazon',
                                        default => null,
                                    };

                                    $isActive = $provider ? \App\Models\ApiIntegration::isProviderActive($provider) : false;
                                    $hasSales = \App\Models\Sale::where('channel', $case)->exists();

                                    if ($isActive || $hasSales) {
                                        $options[$case->value] = $case->getLabel();
                                    }
                                }
                                return $options;
                            }),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->compact(),
            ]);
    }
}
