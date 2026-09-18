<?php

namespace App\Enums;

enum StatusTransacao: string
{
    case SUCESSO = 'sucesso';
    case PENDENTE = 'pendente';
    case FALHOU = 'falhou';
}
