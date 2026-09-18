<?php

namespace App\Enums;

enum FaseCarona: string
{
    case DESCOBERTA = 'descoberta';
    case ATIVA = 'ativa';
    case SUCESSO = 'sucesso';
    case FALHA = 'falha';
}
