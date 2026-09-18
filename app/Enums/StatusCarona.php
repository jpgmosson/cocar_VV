<?php

namespace App\Enums;

enum StatusCarona: string
{
    case ACEITA = 'aceita';
    case MOTORISTA_A_CAMINHO = 'motorista_a_caminho';
    case EM_ANDAMENTO = 'em_andamento';

    case CONCLUIDA = 'concluida';

    case PASSAGEIRO_AUSENTE = 'passageiro_ausente';
    case CANCELADA_MOTORISTA = 'cancelada_motorista';
    case CANCELADA_PASSAGEIRO = 'cancelada_passageiro';

    public function label(): string
    {
        return match ($this) {
            self::ACEITA => 'Aceita',
            self::MOTORISTA_A_CAMINHO => 'Motorista a Caminho',
            self::EM_ANDAMENTO => 'Em Andamento',
            self::CONCLUIDA => 'Concluída',
            self::PASSAGEIRO_AUSENTE => 'Passageiro Ausente',
            self::CANCELADA_MOTORISTA => 'Cancelado pelo Motorista',
            self::CANCELADA_PASSAGEIRO => 'Cancelada pelo Passageiro'
        };
    }

    public function fase(): FaseCarona
    {
        return match ($this) {
            self::MOTORISTA_A_CAMINHO, self::EM_ANDAMENTO, self::ACEITA => FaseCarona::ATIVA,
            self::CONCLUIDA => FaseCarona::SUCESSO,
            self::PASSAGEIRO_AUSENTE, self::CANCELADA_MOTORISTA, self::CANCELADA_PASSAGEIRO => FaseCarona::FALHA
        };
    }

    public static function naFase(FaseCarona $fase): array
    {
        return array_filter(
            self::cases(),
            fn ($status) => $status->fase() === $fase
        );
    }
}
