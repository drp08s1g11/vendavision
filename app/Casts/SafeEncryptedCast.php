<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SafeEncryptedCast implements CastsAttributes
{
    /**
     * Descriptografa o valor com segurança, evitando exceções de MAC inválido caso o APP_KEY tenha mudado.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            Log::warning("Chave de API segura em '{$key}' (ID: {$model->id}) não pôde ser descriptografada com o APP_KEY atual da máquina. Retornando nulo para evitar erro 500.", [
                'exception' => $e->getMessage(),
            ]);

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Criptografa o valor utilizando a chave atual da aplicação.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }
}
