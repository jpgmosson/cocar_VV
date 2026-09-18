<?php

namespace App\Enums;

enum TipoUsuario: string
{
    case ADMINISTRADOR_ORGANIZACAO = 'administrador_organizacao';
    case ADMINISTRADOR_SISTEMA = 'administrador_sistema';
    case PADRAO = 'padrao';

    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRADOR_ORGANIZACAO => 'Administrador de Organização',
            self::ADMINISTRADOR_SISTEMA => 'Administrador do sistema',
            self::PADRAO => 'Padrão',
        };
    }
}
