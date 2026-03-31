<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\ScriptLoader;

class MockScriptLoader implements ScriptLoader
{
    public array $scripts = [];
    public array $styles = [];
    public array $registeredStyles = [];
    public array $inlineScripts = [];

    /**
     * @param array|bool $args
     */
    public function enqueueScript(string $handle, string $url, array $deps, string $version, $args): void
    {
        $this->scripts[$handle] = compact('url', 'deps', 'version', 'args');
    }

    public function enqueueStyle(string $handle, string $url, array $deps, string $version): void
    {
        $this->styles[$handle] = compact('url', 'deps', 'version');
    }

    public function registerStyle(string $handle, string $url, array $deps, string $version): void
    {
        $this->registeredStyles[$handle] = compact('url', 'deps', 'version');
    }

    public function addInlineScript(string $handle, string $code, string $position): void
    {
        if (isset($this->inlineScripts[$handle])) {
            $this->inlineScripts[$handle]['code'] .= "\n" . $code;
        } else {
            $this->inlineScripts[$handle] = compact('code', 'position');
        }
    }
}
