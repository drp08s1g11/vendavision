<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A rota raiz redireciona com segurança para o painel administrativo autenticado.
     */
    public function test_the_application_redirects_root_to_admin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('filament.admin.pages.dashboard'));
    }
}
