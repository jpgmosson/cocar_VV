<?php

namespace App\Enums;

enum StatusPedidoCarona: string
{
    case PROCURANDO_MOTORISTA = 'procurando_motorista';
    case CANCELADO = 'cancelado';
    case ATENDIDO = 'atendido';
    case TIMEOUT = 'timeout';

    public function label(): string
    {
        return match ($this) {
            self::PROCURANDO_MOTORISTA => 'Procurando Motorista',
            self::CANCELADO => 'Cancelado',
            self::ATENDIDO => 'Atendido',
            self::TIMEOUT => 'Timeout'
        };
    }

    public function fase(): FaseCarona
    {
        return match ($this) {
            self::PROCURANDO_MOTORISTA => FaseCarona::DESCOBERTA,
            self::CANCELADO => FaseCarona::FALHA,
            self::ATENDIDO => FaseCarona::ATIVA,
            self::TIMEOUT => FaseCarona::FALHA
        };
    }
}
