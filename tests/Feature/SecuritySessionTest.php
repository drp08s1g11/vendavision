<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SecuritySessionTest extends TestCase
{
    /**
     * Valida que as configurações de segurança de sessão atendem às melhores práticas
     * (LGPD, ISO 27001, OWASP para aplicações financeiras/gerenciais).
     */
    public function test_session_security_settings(): void
    {
        $this->assertEquals(30, config('session.lifetime'), 'O tempo de inatividade da sessão deve ser de 30 minutos.');
        $this->assertTrue(config('session.expire_on_close'), 'A sessão deve expirar imediatamente ao fechar o navegador.');
        $this->assertTrue(config('session.encrypt'), 'Os dados da sessão devem ser criptografados.');
        $this->assertTrue(config('session.http_only'), 'O cookie de sessão deve possuir flag http_only ativa.');
    }

    /**
     * Valida que a tela de login customizada não expõe o campo 'Lembrar de mim'.
     */
    public function test_login_page_renders_without_remember_me(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertDontSee('data.remember');
        $response->assertDontSee('Lembrar de mim');
    }

    /**
     * Valida o bloqueio de login simultâneo: quando um usuário autentica,
     * qualquer sessão prévia existente é sumariamente destruída da tabela de sessões.
     */
    public function test_simultaneous_login_purges_previous_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'single.session@ambrozio.com.br',
            'password' => Hash::make('password123'),
        ]);

        // Simula uma sessão prévia ativa em outro dispositivo/navegador
        $previousSessionId = 'device_a_session_token_' . uniqid();
        DB::table('sessions')->insert([
            'id' => $previousSessionId,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
            'payload' => base64_encode(serialize(['_token' => 'dummy'])),
            'last_activity' => now()->timestamp,
        ]);

        $this->assertDatabaseHas('sessions', [
            'id' => $previousSessionId,
            'user_id' => $user->id,
        ]);

        // Novo login efetuado via componente Livewire do Filament
        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'single.session@ambrozio.com.br',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasNoErrors();

        // A sessão prévia deve ter sido excluída imediatamente
        $this->assertDatabaseMissing('sessions', [
            'id' => $previousSessionId,
        ]);
    }
}
