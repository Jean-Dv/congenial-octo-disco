<?php

declare(strict_types=1);

namespace Moon\ModuleKit\Contracts;

use Moon\ModuleKit\ModuleManifest;

/**
 * Contrato que debe implementar el ServiceProvider de cada modulo
 * (normalmente extendiendo Moon\ModuleKit\AbstractModule en lugar de
 * implementar esto a mano). Es la unica pieza de codigo PHP que el
 * ModuleManager necesita para reconocer un modulo como tal.
 */
interface ModuleInterface
{
    /**
     * Identidad declarativa del modulo (debe coincidir con su module.json).
     */
    public function manifest(): ModuleManifest;
}
