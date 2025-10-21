<?php

namespace App\Enums;

enum PackagePriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    /**
     * Devuelve todos los valores de las prioridades.
     * Por ejemplo siempre retornara -> ['low', 'medium', 'high'].
     * @return array<int, string>
     */
    public static function values(): array
    {
       return array_column(self::cases(), 'value');
    }
}
