<?php

namespace App\Filament\Pages\Auth;

use App\Rules\StrongPasswordRule;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Nova Senha')
            ->password()
            ->revealable()
            ->rule(new StrongPasswordRule())
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->helperText('Critérios: Mínimo 14 caracteres, letra maiúscula, minúscula, número e caractere especial (!@#$%&*).')
            ->same('passwordConfirmation');
    }
}
