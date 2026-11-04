<?php

namespace App\Filament\Pages;

use App\Models\ApiIntegration;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class ManageIntegrations extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Canais & APIs (Marketplaces)';
    protected static ?string $title = 'Gerenciamento de Canais & APIs de Marketplaces';
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.manage-integrations';

    public bool $isUnlocked = false;
    public string $confirmPassword = '';

    public ?array $data = [];

    public function mount(): void
    {
        // Se em ambiente local ou já desbloqueado nesta sessão, permite edição direta
        if (session('integrations_vault_unlocked') || app()->environment('local')) {
            $this->isUnlocked = true;
            $this->loadIntegrationsData();
        }
    }

    /**
     * Valida a senha do usuário logado para conceder acesso
     */
    public function unlockVault(): void
    {
        $user = auth()->user();

        if (!$user || !Hash::check($this->confirmPassword, $user->password)) {
            Notification::make()
                ->title('Senha incorreta')
                ->body('Acesso negado às credenciais das APIs.')
                ->danger()
                ->send();
            return;
        }

        $this->isUnlocked = true;
        $this->confirmPassword = '';
        session(['integrations_vault_unlocked' => true]);

        $this->loadIntegrationsData();

        Notification::make()
            ->title('Cofre Desbloqueado com Sucesso')
            ->body('As credenciais criptografadas estão prontas para edição.')
            ->success()
            ->send();
    }

    public function lockVault(): void
    {
        $this->isUnlocked = false;
        session()->forget('integrations_vault_unlocked');

        Notification::make()
            ->title('Cofre Bloqueado')
            ->info()
            ->send();
    }

    protected function loadIntegrationsData(): void
    {
        $integrations = ApiIntegration::all()->toArray();
        $this->form->fill(['integrations' => $integrations]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('integrations')
                    ->label('Contas de Marketplaces Conectadas')
                    ->addActionLabel('+ Adicionar Nova Integração (Shopee, Amazon, etc.)')
                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'Nova API') . ' (' . ($state['provider'] ?? 'personalizada') . ')')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome Identificador')
                                ->placeholder('Ex: Mercado Livre Principal / Amazon Decora')
                                ->required(),

                            Forms\Components\Select::make('provider')
                                ->label('Plataforma')
                                ->options([
                                    'mercadolivre' => 'Mercado Livre',
                                    'shopee' => 'Shopee Open Platform',
                                    'amazon' => 'Amazon SP-API',
                                    'magalu' => 'Magazine Luiza (Magalu)',
                                    'shein' => 'Shein Marketplace',
                                    'custom' => 'Outra API REST Personalizada',
                                ])
                                ->required(),

                            Forms\Components\Select::make('environment')
                                ->label('Ambiente')
                                ->options([
                                    'production' => 'Produção (Vendas Reais)',
                                    'sandbox' => 'Ambiente de Testes (Sandbox)',
                                ])
                                ->default('production')
                                ->required(),
                        ]),

                        Forms\Components\Section::make('Chaves de Acesso e Credenciais')
                            ->description('Os dados salvos aqui são armazenados com criptografia no banco de dados.')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('client_id')
                                    ->label('App ID / Client ID / Partner ID')
                                    ->placeholder('Identificador fornecido pelo marketplace')
                                    ->required(),

                                Forms\Components\TextInput::make('seller_id')
                                    ->label('Seller ID / Shop ID / ID da Loja')
                                    ->placeholder('Ex: ID da sua loja parceira')
                                    ->required(),

                                Forms\Components\TextInput::make('client_secret')
                                    ->label('Client Secret / Partner Key')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Chave secreta privada'),

                                Forms\Components\TextInput::make('access_token')
                                    ->label('Access Token / Refresh Token')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Token de autorização OAuth2'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Integração Ativa no Sistema')
                            ->helperText('Quando ativo, os botões deste marketplace ficarão visíveis na listagem de Vendas e filtros do Dashboard.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $integrations = $state['integrations'] ?? [];

        // Remove integrações excluídas
        $currentIds = collect($integrations)->pluck('id')->filter();
        ApiIntegration::whereNotIn('id', $currentIds)->delete();

        // Atualiza ou cria as integrações
        foreach ($integrations as $item) {
            ApiIntegration::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'name' => $item['name'],
                    'provider' => $item['provider'],
                    'environment' => $item['environment'],
                    'client_id' => $item['client_id'] ?? null,
                    'seller_id' => $item['seller_id'] ?? null,
                    'client_secret' => $item['client_secret'] ?? null,
                    'access_token' => $item['access_token'] ?? null,
                    'is_active' => (bool) ($item['is_active'] ?? true),
                ]
            );
        }

        Notification::make()
            ->title('Credenciais Salvas com Sucesso')
            ->body('Todas as chaves de API foram criptografadas e atualizadas.')
            ->success()
            ->send();

        $this->loadIntegrationsData();
    }
}