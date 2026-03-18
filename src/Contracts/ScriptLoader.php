<?php

namespace Completionist\Contracts;

interface ScriptLoader
{
    /**
     * @param array|bool $args In footer (bool) or args array with 'in_footer' and 'strategy' keys
     */
    public function enqueueScript(string $handle, string $url, array $deps, string $version, $args): void;
    public function enqueueStyle(string $handle, string $url, array $deps, string $version): void;
    public function registerStyle(string $handle, string $url, array $deps, string $version): void;
    public function addInlineScript(string $handle, string $code, string $position): void;
}
