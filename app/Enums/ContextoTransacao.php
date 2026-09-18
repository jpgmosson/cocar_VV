<?php

namespace App\Enums;

enum ContextoTransacao: string
{
    case NENHUM = 'nenhum';
    case TRAJETO = 'trajeto';
    case PEDIDO_CARONA = 'pedido_carona';
}
