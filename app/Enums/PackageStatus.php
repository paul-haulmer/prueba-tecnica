<?php

namespace App\Enums;

enum PackageStatus: string
{
    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';

    /**
     * Devuelve todos los valores de los status de un paquete.
     * Por ejemplo siempre retornara -> ['pending', 'in_transit', 'delivered'].
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
