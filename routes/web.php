<?php

use Illuminate\Support\Facades\Route;

// Redireciona a rota pública raiz diretamente para o painel seguro do Filament
Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});
