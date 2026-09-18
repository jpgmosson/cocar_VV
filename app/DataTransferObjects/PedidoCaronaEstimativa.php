<?php

namespace App\DataTransferObjects;

class PedidoCaronaEstimativa
{
    public function __construct(
        public string $min,
        public string $max,
        public string $saldoUsuario,
        public bool $saldoUsuarioSuficiente
    ) {}
}
