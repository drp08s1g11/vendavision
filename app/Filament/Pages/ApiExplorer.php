<?php

namespace App\Filament\Pages;

use App\Models\ApiIntegration;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class ApiExplorer extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Explorador da API Shopee';
    protected static ?string $title = 'Inspetor de Retorno de Dados • Shopee Open Platform API';
    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.api-explorer';

    public ?array $formData = [];
    public ?array $responseResult = null;
    public bool $hasExecuted = false;

    public function mount(): void
    {
        $integration = ApiIntegration::where('provider', 'shopee')->first();

        $this->form->fill([
            'partner_id' => $integration?->client_id ?? '',
            'shop_id' => $integration?->seller_id ?? '',
            'partner_key' => $integration?->client_secret ?? '',
            'access_token' => $integration?->access_token ?? '',
            'api_path' => '/api/v2/order/get_order_list',
            'days_back' => 15,
            'page_size' => 20,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Parâmetros da Consulta na Shopee')
                    ->description('Credenciais carregadas automaticamente do Cofre de APIs.')
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('partner_id')
                            ->label('Partner ID / App ID')
                            ->required(),

                        Forms\Components\TextInput::make('shop_id')
                            ->label('Shop ID (ID da Loja)')
                            ->required(),

                        Forms\Components\TextInput::make('partner_key')
                            ->label('Partner Key / Secret')
                            ->password()
                            ->revealable(),

                        Forms\Components\TextInput::make('access_token')
                            ->label('Access Token (OAuth2)')
                            ->password()
                            ->revealable()
                            ->helperText('Opcional para APIs públicas, obrigatório para ler pedidos da loja.'),

                        Forms\Components\Select::make('api_path')
                            ->label('Endpoint / Recurso a Testar')
                            ->options([
                                '/api/v2/order/get_order_list' => 'Lista de Pedidos (/api/v2/order/get_order_list)',
                                '/api/v2/order/get_order_detail' => 'Detalhes de um Pedido (/api/v2/order/get_order_detail)',
                                '/api/v2/shop/get_shop_info' => 'Dados da Loja (/api/v2/shop/get_shop_info)',
                                '/api/v2/product/get_item_list' => 'Lista de Itens/Anúncios (/api/v2/product/get_item_list)',
                            ])
                            ->default('/api/v2/order/get_order_list')
                            ->columnSpan(2)
                            ->required(),

                        Forms\Components\Select::make('days_back')
                            ->label('Período de Busca')
                            ->options([
                                1 => 'Últimas 24 horas',
                                3 => 'Últimos 3 dias',
                                7 => 'Últimos 7 dias',
                                15 => 'Últimos 15 dias (Limite máximo por chamada da Shopee)',
                            ])
                            ->default(15)
                            ->helperText('A API da Shopee limita cada consulta a uma janela máxima de 15 dias.'),

                        Forms\Components\TextInput::make('page_size')
                            ->label('Limite de Registros')
                            ->numeric()
                            ->default(20),
                    ]),
            ])
            ->statePath('formData');
    }

    /**
     * Executa a requisição real à API da Shopee com cálculo de assinatura HMAC-SHA256 oficial
     */
    public function executeRequest(): void
    {
        $data = $this->form->getState();

        $partnerId = (int) trim($data['partner_id']);
        $shopId = (int) trim($data['shop_id']);
        $partnerKey = trim($data['partner_key'] ?? '');
        $accessToken = trim($data['access_token'] ?? '');
        $path = trim($data['api_path']);
        $daysBack = (int) ($data['days_back'] ?? 15);
        $pageSize = (int) ($data['page_size'] ?? 20);

        $timestamp = time();
        $host = 'https://partner.shopeemobile.com';

        // Geração da Assinatura Oficial da Shopee v2:
        // HMAC-SHA256(partner_id + path + timestamp + access_token + shop_id, partner_key)
        $signBase = "{$partnerId}{$path}{$timestamp}";
        if ($accessToken) {
            $signBase .= $accessToken;
        }
        if ($shopId) {
            $signBase .= $shopId;
        }

        $sign = hash_hmac('sha256', $signBase, $partnerKey);

        $queryParams = [
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        if ($shopId) {
            $queryParams['shop_id'] = $shopId;
        }
        if ($accessToken) {
            $queryParams['access_token'] = $accessToken;
        }

        if ($path === '/api/v2/order/get_order_list') {
            $queryParams['time_range_field'] = 'create_time';
            $queryParams['time_from'] = $timestamp - ($daysBack * 86400);
            $queryParams['time_to'] = $timestamp;
            $queryParams['page_size'] = $pageSize;
        }

        $requestUrl = $host . $path;

        try {
            $startTime = microtime(true);
            $response = Http::timeout(15)->get($requestUrl, $queryParams);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $body = $response->json() ?? $response->body();

            $this->responseResult = [
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'request_url' => $requestUrl,
                'query_sent' => $queryParams,
                'response_headers' => $response->headers(),
                'response_body' => $body,
                'is_success' => $response->successful(),
            ];

            $this->hasExecuted = true;

            Notification::make()
                ->title('Resposta Recebida da Shopee!')
                ->body("Código HTTP {$response->status()} em {$duration}ms.")
                ->color($response->successful() ? 'success' : 'warning')
                ->send();
        } catch (\Exception $e) {
            $this->responseResult = [
                'status_code' => 500,
                'duration_ms' => 0,
                'request_url' => $requestUrl,
                'query_sent' => $queryParams,
                'error' => $e->getMessage(),
                'is_success' => false,
            ];
            $this->hasExecuted = true;

            Notification::make()
                ->title('Falha de Comunicação')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
