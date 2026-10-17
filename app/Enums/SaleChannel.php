<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SaleChannel: string implements HasLabel, HasColor
{
    case MercadoLivre = 'mercado_livre';
    case Shopee = 'shopee';
    case Amazon = 'amazon';
    case Direct = 'direct';

    public function getLabel(): string
    {
        return match ($this) {
            self::MercadoLivre => 'Mercado Livre',
            self::Shopee => 'Shopee',
            self::Amazon => 'Amazon',
            self::Direct => 'Venda Direta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MercadoLivre => 'warning',
            self::Shopee => 'danger',
            self::Amazon => 'info',
            self::Direct => 'success',
        };
    }

    /** Comissão padrão estimada (%) – usada como sugestão no formulário. */
    public function defaultFeePercent(): float
    {
        return match ($this) {
            self::MercadoLivre => 16.0,
            self::Shopee => 20.0,
            self::Amazon => 15.0,
            self::Direct => 0.0,
        };
    }
}
