<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'seller_id',
        'environment',
        'extra_fields',
        'is_active',
        'last_synced_at',
    ];

    /**
     * Criptografa automaticamente credenciais sensíveis no MySQL
     */
    protected $casts = [
        'client_secret' => 'encrypted',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'extra_fields' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Verifica se determinado provedor/marketplace está ativo no sistema.
     */
    public static function isProviderActive(string $provider): bool
    {
        return static::where('provider', $provider)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Retorna a lista de provedores/marketplaces com integração ativa.
     *
     * @return array<string>
     */
    public static function getActiveProviders(): array
    {
        return static::where('is_active', true)
            ->pluck('provider')
            ->toArray();
    }
}