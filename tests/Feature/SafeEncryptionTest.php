<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageIntegrations;
use App\Models\ApiIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SafeEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_encrypted_cast_encrypts_and_decrypts_normally(): void
    {
        $integration = ApiIntegration::create([
            'name' => 'Shopee Loja 1',
            'provider' => 'shopee',
            'client_id' => '123456',
            'client_secret' => 'minha_chave_secreta_super_segura',
            'access_token' => 'token_de_acesso_123',
            'is_active' => true,
        ]);

        $this->assertEquals('minha_chave_secreta_super_segura', $integration->client_secret);
        $this->assertEquals('token_de_acesso_123', $integration->access_token);

        // No banco bruto deve estar criptografado (diferente da string original)
        $raw = DB::table('api_integrations')->where('id', $integration->id)->first();
        $this->assertNotEquals('minha_chave_secreta_super_segura', $raw->client_secret);
        $this->assertStringStartsWith('eyJ', $raw->client_secret); // Base64 JSON do payload criptografado
    }

    public function test_corrupted_or_different_app_key_does_not_crash_with_decrypt_exception(): void
    {
        // Insere registro diretamente no banco com payload criptográfico inválido/MAC adulterado
        $invalidEncryptedPayload = base64_encode(json_encode([
            'iv' => base64_encode('1234567890123456'),
            'value' => base64_encode('dados_com_outra_chave'),
            'mac' => hash_hmac('sha256', 'iv_alterado', 'chave_inexistente'),
            'tag' => '',
        ]));

        $id = DB::table('api_integrations')->insertGetId([
            'name' => 'Mercado Livre Antigo',
            'provider' => 'mercadolivre',
            'client_id' => '78910',
            'client_secret' => $invalidEncryptedPayload,
            'access_token' => $invalidEncryptedPayload,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Não deve estourar DecryptException: The MAC is invalid
        $integration = ApiIntegration::find($id);
        $this->assertNotNull($integration);
        $this->assertNull($integration->client_secret, 'Deve retornar null com segurança em vez de lançar exceção 500.');
        $this->assertNull($integration->access_token);

        // toArray() e páginas administrativas devem carregar sem erro
        $array = ApiIntegration::all()->toArray();
        $this->assertIsArray($array);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ManageIntegrations::class)
            ->assertSuccessful();
    }
}
