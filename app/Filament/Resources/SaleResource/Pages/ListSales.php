<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\ApiIntegration;
use App\Services\MarketplaceSyncService;
use App\Services\SaleBackupService;
use App\Services\ShopeeReportImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botão A: Importar Planilha de Rendimento Shopee (CSV oficial exportado do Seller Center)
            Actions\Action::make('importShopeeReport')
                ->label('Importar Relatório Shopee (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->visible(fn () => ApiIntegration::isProviderActive('shopee'))
                ->modalHeading('Importar Relatório de Rendimento da Shopee')
                ->modalDescription('Faça upload do arquivo CSV exportado da sua Carteira/Rendimento no Shopee Seller Center. O sistema reconhecerá automaticamente todos os pedidos, taxas de comissão, métodos de pagamento e produtos.')
                ->modalSubmitActionLabel('Processar Relatório Agora')
                ->form([
                    Forms\Components\FileUpload::make('report_file')
                        ->label('Selecione o arquivo do Relatório (.CSV)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                        ->disk('local')
                        ->directory('temp-shopee-reports')
                        ->required(),
                ])
                ->action(function (array $data, ShopeeReportImportService $importService) {
                    $filePath = Storage::disk('local')->path($data['report_file']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('Arquivo não encontrado')
                            ->danger()
                            ->send();
                        return;
                    }

                    $content = file_get_contents($filePath);
                    $result = $importService->importFromCsvContent($content);

                    // Limpa o arquivo temporário
                    @unlink($filePath);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Relatório Shopee Importado!')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erro ao processar relatório')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),

            // Botão B: Importar Backup de Vendas (Excel / CSV) com deduplicação comparativa
            Actions\Action::make('importBackupSales')
                ->label('Importar Vendas (Backup CSV)')
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->color('success')
                ->modalHeading('Importar Backup de Vendas (Excel / CSV)')
                ->modalDescription('Restaure ou importe uma planilha de vendas prévia. Cada pedido é comparado pelo Código do Pedido: pedidos já existentes com mesmos valores são preservados e NÃO são duplicados.')
                ->modalSubmitActionLabel('Importar e Comparar Vendas')
                ->form([
                    Forms\Components\FileUpload::make('backup_file')
                        ->label('Selecione o arquivo de Backup (.CSV)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                        ->disk('local')
                        ->directory('temp-sales-backups')
                        ->required(),
                ])
                ->action(function (array $data, SaleBackupService $backupService) {
                    $filePath = Storage::disk('local')->path($data['backup_file']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('Arquivo não encontrado')
                            ->danger()
                            ->send();
                        return;
                    }

                    $content = file_get_contents($filePath);
                    $result = $backupService->importFromCsvContent($content);

                    @unlink($filePath);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Backup de Vendas Processado!')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erro ao processar backup')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),

            // Botão 0: Sincronizar Tudo via API (Aparece somente quando há 2 ou mais APIs ativas)
            Actions\Action::make('syncAll')
                ->label('Sincronizar Todas as APIs')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->visible(fn () => count(ApiIntegration::getActiveProviders()) > 1)
                ->form([
                    Forms\Components\Select::make('days_back')
                        ->label('Período de busca')
                        ->options([
                            15 => 'Últimos 15 dias',
                            7 => 'Últimos 7 dias',
                            3 => 'Últimos 3 dias',
                            1 => 'Últimas 24 horas',
                        ])
                        ->default(15)
                        ->required(),
                ])
                ->modalHeading('Sincronização Completa de Canais Ativos')
                ->modalDescription(function () {
                    $providers = ApiIntegration::getActiveProviders();
                    $names = array_map(fn ($p) => ucfirst($p), $providers);
                    return 'O Venda Vision fará a sincronização nos marketplaces ativos (' . implode(', ', $names) . ') e atualizará custos, taxas e margens.';
                })
                ->modalSubmitActionLabel('Sincronizar Agora')
                ->action(function (array $data, MarketplaceSyncService $syncService) {
                    $result = $syncService->syncAll((int) $data['days_back']);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Sincronização Global Concluída!')
                            ->body("Varredura realizada com sucesso ({$result['total_orders']} pedidos sincronizados).")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Atenção na Sincronização')
                            ->body('Não foi possível sincronizar todos os pedidos. Verifique as credenciais no painel.')
                            ->danger()
                            ->send();
                    }
                }),

            // Botão 1: Sincronizar Mercado Livre via API (Apenas se a integração estiver ativa)
            Actions\Action::make('syncMeli')
                ->label('Mercado Livre (API)')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => ApiIntegration::isProviderActive('mercadolivre'))
                ->form([
                    Forms\Components\Select::make('days_back')
                        ->label('Período de busca')
                        ->options([
                            1 => 'Últimas 24 horas',
                            3 => 'Últimos 3 dias',
                            7 => 'Últimos 7 dias',
                            15 => 'Últimos 15 dias',
                            30 => 'Últimos 30 dias',
                        ])
                        ->default(15)
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

            // Botão 2: Sincronizar Shopee via API (Apenas se a integração estiver ativa)
            Actions\Action::make('syncShopee')
                ->label('Shopee (API)')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->visible(fn () => ApiIntegration::isProviderActive('shopee'))
                ->form([
                    Forms\Components\Select::make('days_back')
                        ->label('Período de busca')
                        ->options([
                            15 => 'Últimos 15 dias (Limite máximo da Shopee)',
                            7 => 'Últimos 7 dias',
                            3 => 'Últimos 3 dias',
                            1 => 'Últimas 24 horas',
                        ])
                        ->default(15)
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
