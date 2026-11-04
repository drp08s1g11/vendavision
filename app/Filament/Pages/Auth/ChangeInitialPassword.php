<?php

namespace App\Filament\Pages\Auth;

use App\Rules\StrongPasswordRule;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class ChangeInitialPassword extends Page
{
    protected static ?string $slug = 'redefinir-senha-inicial';

    protected static ?string $title = 'Definição de Nova Senha Obrigatória';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.auth.change-initial-password';

    protected static string $layout = 'filament-panels::components.layout.simple';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        if (! auth()->check()) {
            redirect()->to(Filament::getLoginUrl());
            return;
        }

        if (! auth()->user()->must_change_password) {
            redirect()->to(Filament::getUrl());
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Definição de Nova Senha Obrigatória';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Definição de Nova Senha Obrigatória';
    }

    public function hasLogo(): bool
    {
        return true;
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Você está utilizando uma senha temporária. Para desbloquear o sistema, cadastre sua nova senha permanente atendendo a todos os critérios de segurança.';
    }

    /* ---------- Helpers de Validação em Tempo Real para o Checklist ---------- */

    public function getHasMinLengthProperty(): bool
    {
        return mb_strlen($this->new_password) >= 14;
    }

    public function getHasUppercaseProperty(): bool
    {
        return (bool) preg_match('/[A-Z]/', $this->new_password);
    }

    public function getHasLowercaseProperty(): bool
    {
        return (bool) preg_match('/[a-z]/', $this->new_password);
    }

    public function getHasNumberProperty(): bool
    {
        return (bool) preg_match('/[0-9]/', $this->new_password);
    }

    public function getHasSpecialProperty(): bool
    {
        return (bool) preg_match('/[!@#$%&*]/', $this->new_password);
    }

    public function getHasMatchedConfirmationProperty(): bool
    {
        return $this->new_password !== '' && $this->new_password === $this->new_password_confirmation;
    }

    public function getIsAllCriteriaMetProperty(): bool
    {
        return $this->hasMinLength
            && $this->hasUppercase
            && $this->hasLowercase
            && $this->hasNumber
            && $this->hasSpecial
            && $this->hasMatchedConfirmation;
    }

    public function save(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', new StrongPasswordRule()],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ], [
            'current_password.required' => 'Informe a senha temporária atual.',
            'current_password.current_password' => 'A senha temporária atual informada está incorreta.',
            'new_password.required' => 'Digite a sua nova senha.',
            'new_password_confirmation.required' => 'Confirme a sua nova senha.',
            'new_password_confirmation.same' => 'A confirmação de senha não confere com a nova senha digitada.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->new_password),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Senha Registrada com Sucesso!')
            ->body('Sua credencial permanente foi ativada. Bem-vindo ao VendaVision!')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }

    public function logout(): void
    {
        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(Filament::getLoginUrl());
    }
}
