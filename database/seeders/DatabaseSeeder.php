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
        User::firstOrCreate(
            ['email' => 'admin@ambrozio.com.br'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // Caso deseje popular o sistema com o catálogo e vendas de demonstração:
        // php artisan db:seed --class=AmbrozioDecoraSeeder
    }
}
