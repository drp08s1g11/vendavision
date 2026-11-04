<?php

use Illuminate\Support\Facades\Route;

// Redireciona a rota pública raiz diretamente para o painel seguro do Filament
Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Alias da rota padrão 'login' do Laravel apontando para o login seguro do Filament
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');
