<?php

namespace Completionist\Contracts;

interface ScriptLoader {
    public function enqueueScript(string $handle, string $url, array $deps, string $version, bool $inFooter): void;
    public function enqueueStyle(string $handle, string $url, array $deps, string $version): void;
    public function addInlineScript(string $handle, string $code, string $position): void;
}