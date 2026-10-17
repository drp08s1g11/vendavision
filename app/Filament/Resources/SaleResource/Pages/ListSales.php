<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\MarketplaceSyncService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botão 1: Sincronizar Mercado Livre
            Actions\Action::make('syncMeli')
                ->label('Sincronizar Mercado Livre')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('days_back')
                        ->label('Período de busca')
                        ->options([
                            1 => 'Últimas 24 horas',
                            3 => 'Últimos 3 dias',
                            7 => 'Últimos 7 dias',
                            15 => 'Últimos 15 dias',
                        ])
                        ->default(3)
                        ->required(),
                ])
                ->modalHeading('Sincronização Mercado Livre API')
                ->modalDescription('O Venda Vision buscará novos pedidos, calculará a comissão de 16% e congelará os custos de fornecedor.')
                ->modalSubmitActionLabel('Buscar Pedidos Agora')
                ->action(function (array $data, MarketplaceSyncService $syncService) {
                    $result = $syncService->syncMercadoLivre((int) $data['days_back']);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Mercado Livre Sincronizado!')
                            ->body("Pedido {$result['order_code']} importado com taxas apuradas com sucesso.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erro na Sincronização')
                            ->body($result['message'] ?? 'Falha ao buscar pedidos.')
                            ->danger()
                            ->send();
                    }
                }),

            // Botão 2: Sincronizar Shopee
            Actions\Action::make('syncShopee')
                ->label('Sincronizar Shopee')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->form([
                    Forms\Components\Select::make('days_back')
                        ->label('Período de busca')
                        ->options([
                            1 => 'Últimas 24 horas',
                            3 => 'Últimos 3 dias',
                            7 => 'Últimos 7 dias',
                            15 => 'Últimos 15 dias',
                        ])
                        ->default(3)
                        ->required(),
                ])
                ->modalHeading('Sincronização Shopee Open Platform API')
                ->modalDescription('O Venda Vision buscará novos pedidos da Shopee, deduzirá as taxas de comissão (20%) e registrará a apuração real de lucro.')
                ->modalSubmitActionLabel('Buscar Pedidos Agora')
                ->action(function (array $data, MarketplaceSyncService $syncService) {
                    $result = $syncService->syncShopee((int) $data['days_back']);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Shopee Sincronizada!')
                            ->body("Pedido {$result['order_code']} importado e registrado com sucesso.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erro na Sincronização')
                            ->body($result['message'] ?? 'Falha ao buscar pedidos.')
                            ->danger()
                            ->send();
                    }
                }),

            // Botão 3: Cadastrar Venda Manualmente
            Actions\CreateAction::make()
                ->label('Nova Venda Manual'),
        ];
    }
}