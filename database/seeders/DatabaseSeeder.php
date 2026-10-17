<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuário Administrador Oficial para Demonstração e Avaliação
        User::firstOrCreate(
            ['email' => 'admin@ambrozio.com.br'],
            [
                'name' => 'Alan Cesar Ambrozio',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Povoamento Completo com Vendas, Itens e Catálogo Real da Ambrozio Decora
        $this->call([
            AmbrozioDecoraSeeder::class,
        ]);
    }
}
