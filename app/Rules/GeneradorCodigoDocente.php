<?php

namespace App\Rules;

use App\Models\Users\User;

class GeneradorCodigoDocente
{
    public static function generar(User $user): string
    {
        $limpio = preg_replace('/[^\p{L}\s]/u', '', trim($user->name));
        $partes = preg_split('/\s+/', $limpio);

        $iniciales = '';
        if (count($partes) >= 3) {
            $iniciales = mb_substr($partes[count($partes) - 2], 0, 1) . mb_substr($partes[count($partes) - 1], 0, 1);
        } elseif (count($partes) === 2) {
            $iniciales = mb_substr($partes[1], 0, 2);
        } else {
            $iniciales = mb_substr($partes[0] . 'XX', 0, 2);
        }

        $iniciales = mb_strtoupper($iniciales, 'UTF-8');
        $correlativo = str_pad((string) $user->id, 8, '0', STR_PAD_LEFT);

        return "D".$iniciales.$correlativo;
    }
}