<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\DB;

class Login extends BaseLogin
{
    /**
     * Customiza o formulário de login para remover a opção 'Lembrar de mim' (Remember Me).
     * Em sistemas de gestão comercial e financeira, sessões persistentes indeterminadas
     * são evitadas por conformidade com segurança da informação e LGPD.
     *
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Autenticação com bloqueio de login simultâneo (Single Active Session Enforcement).
     * Garante que apenas uma sessão ativa por usuário exista a qualquer momento.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        // 1. Invalida outros dispositivos no nível do guard (AuthenticateSession)
        if (! empty($data['password'])) {
            try {
                Filament::auth()->logoutOtherDevices($data['password']);
            } catch (\Throwable $e) {
                // Silêncio defensivo caso guard não suporte rehash
            }
        }

        // 2. Bloqueio absoluto de sessões simultâneas na tabela 'sessions':
        // Purga qualquer outra sessão ativa deste usuário (exceto a sessão atual recém-gerada)
        $currentSessionId = session()->getId();
        $purgedSessions = DB::table('sessions')
            ->where('user_id', $user->getAuthIdentifier())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        // 3. Se outras sessões foram destruídas, alerta o usuário logado
        if ($purgedSessions > 0) {
            Notification::make()
                ->warning()
                ->title('Atenção: Acesso simultâneo bloqueado')
                ->body('Identificamos outra sessão ativa com este usuário que foi desconectada imediatamente para garantir a sessão única.')
                ->persistent()
                ->send();
        }

        return app(LoginResponse::class);
    }
}
