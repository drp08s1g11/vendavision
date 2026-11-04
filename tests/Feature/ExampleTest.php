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

    public function test_unauthenticated_user_accessing_admin_route_is_redirected_to_filament_login(): void
    {
        $response = $this->get('/admin/sales');

        $response->assertRedirect('/admin/login');
    }

    public function test_standard_login_route_redirects_to_filament_login(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/admin/login');
    }
}
