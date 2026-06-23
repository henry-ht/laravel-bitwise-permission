<?php

namespace HenryHt\BitwisePermission\Helpers;

/**
 * BitwiseHelper
 *
 * Utilidades para trabajar con permisos bitwise.
 * Uso: BitwiseHelper::combine(['view', 'create', 'update'])
 */
class BitwiseHelper
{
    /**
     * Combina múltiples bits por nombre y retorna el valor entero.
     *
     * Ejemplo:
     *   BitwiseHelper::combine(['view', 'create', 'update']) → 13
     */
    public static function combine(array $bitNames): int
    {
        $bits   = config('bitwise-permission.bits', []);
        $result = 0;

        foreach ($bitNames as $name) {
            $result |= $bits[$name] ?? 0;
        }

        return $result;
    }

    /**
     * Retorna los nombres de bits activos en un valor de acceso.
     *
     * Ejemplo:
     *   BitwiseHelper::decode(13) → ['view', 'create', 'update']
     */
    public static function decode(int $access): array
    {
        $bits   = config('bitwise-permission.bits', []);
        $active = [];

        foreach ($bits as $name => $bit) {
            if (($access & $bit) === $bit) {
                $active[] = $name;
            }
        }

        return $active;
    }

    /**
     * Verifica si un valor de acceso tiene un bit específico.
     *
     * Ejemplo:
     *   BitwiseHelper::has(13, 'create') → true
     */
    public static function has(int $access, string $bitName): bool
    {
        $bit = config("bitwise-permission.bits.{$bitName}", 0);
        return $bit > 0 && ($access & $bit) === $bit;
    }

    /**
     * Agrega un bit a un valor de acceso existente.
     */
    public static function add(int $access, string $bitName): int
    {
        $bit = config("bitwise-permission.bits.{$bitName}", 0);
        return $access | $bit;
    }

    /**
     * Quita un bit de un valor de acceso existente.
     */
    public static function remove(int $access, string $bitName): int
    {
        $bit = config("bitwise-permission.bits.{$bitName}", 0);
        return $access & ~$bit;
    }

    /**
     * Retorna el valor total (todos los bits activos).
     */
    public static function total(): int
    {
        return array_sum(config('bitwise-permission.bits', []));
    }

    /**
     * Retorna todos los bits disponibles con su valor.
     */
    public static function all(): array
    {
        return config('bitwise-permission.bits', []);
    }
}
