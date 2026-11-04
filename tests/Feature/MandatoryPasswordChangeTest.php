<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\ChangeInitialPassword;
use App\Models\User;
use App\Rules\StrongPasswordRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class MandatoryPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_temporary_password_is_redirected_to_change_password_page(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@vendavision.com',
            'password' => Hash::make('password'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        // Tentar acessar o dashboard deve redirecionar para a redefinição obrigatória
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/redefinir-senha-inicial');

        // Tentar acessar produtos também deve redirecionar
        $responseProd = $this->get('/admin/products');
        $responseProd->assertRedirect('/admin/redefinir-senha-inicial');

        // Acessar a própria página de redefinição deve ser permitido
        $responsePage = $this->get('/admin/redefinir-senha-inicial');
        $responsePage->assertSuccessful();
    }

    public function test_strong_password_rule_validates_all_criteria(): void
    {
        $rule = new StrongPasswordRule();

        // 1. Menos de 14 caracteres
        $fail1 = null;
        $rule->validate('password', 'Senha@123', function ($msg) use (&$fail1) {
            $fail1 = $msg;
        });
        $this->assertStringContainsString('14 caracteres', (string) $fail1);

        // 2. Sem letra maiúscula (14+ caracteres)
        $fail2 = null;
        $rule->validate('password', 'senhafraca@12345', function ($msg) use (&$fail2) {
            $fail2 = $msg;
        });
        $this->assertStringContainsString('maiúscula', (string) $fail2);

        // 3. Sem número
        $fail3 = null;
        $rule->validate('password', 'SenhaForteSemNumero@!', function ($msg) use (&$fail3) {
            $fail3 = $msg;
        });
        $this->assertStringContainsString('número', (string) $fail3);

        // 4. Sem caractere especial permitido (!@#$%&*)
        $fail4 = null;
        $rule->validate('password', 'SenhaForte12345678', function ($msg) use (&$fail4) {
            $fail4 = $msg;
        });
        $this->assertStringContainsString('caractere especial', (string) $fail4);

        // 5. Senha válida atendendo 100% dos requisitos
        $failValid = null;
        $rule->validate('password', 'VendaVision@2026!', function ($msg) use (&$failValid) {
            $failValid = $msg;
        });
        $this->assertNull($failValid, 'A senha válida não deve retornar erros de validação.');
    }

    public function test_user_can_successfully_set_new_password_and_access_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@vendavision.com',
            'password' => Hash::make('password'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ChangeInitialPassword::class)
            ->set('current_password', 'password')
            ->set('new_password', 'AmbrozioDecora#2026')
            ->set('new_password_confirmation', 'AmbrozioDecora#2026')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect('/admin');

        $user->refresh();

        $this->assertFalse($user->must_change_password, 'must_change_password deve ser falso após a alteração.');
        $this->assertTrue(Hash::check('AmbrozioDecora#2026', $user->password));

        // Agora o usuário pode navegar livremente pelo painel sem redirecionamento
        $responseDashboard = $this->get('/admin');
        $responseDashboard->assertSuccessful();
    }
}
