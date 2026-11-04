<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Cria apenas o usuário administrador inicial, mantendo a base 100% limpa
     * de produtos e vendas para que o usuário cadastre tudo do zero.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vendavision.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'must_change_password' => true,
            ]
        );

        // Remove usuário legado anterior se existir
        User::where('email', 'admin@ambrozio.com.br')->delete();

        // Caso deseje popular o sistema com o catálogo e vendas de demonstração:
        // php artisan db:seed --class=AmbrozioDecoraSeeder
    }
}
