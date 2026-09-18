<?php

namespace App\Enums;

enum StatusTrajeto: string
{
    case PLANEJADO = 'planejado';
    case EM_ANDAMENTO = 'em_andamento';
    case CONCLUIDO = 'concluido';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PLANEJADO => 'Planejado',
            self::EM_ANDAMENTO => 'Em Andamento',
            self::CONCLUIDO => 'Concluido',
            self::CANCELADO => 'Cancelado'
        };
    }
}
