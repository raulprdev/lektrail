<?php

namespace Completionist\Tests\Mocks;

use Completionist\ScriptLoader;

class MockScriptLoader implements ScriptLoader {

    public array $scripts = [];
    public array $styles = [];
    public array $inlineScripts = [];

    public function enqueueScript(string $handle, string $url, array $deps, string $version, bool $inFooter): void {
        $this->scripts[$handle] = compact('url', 'deps', 'version', 'inFooter');
    }

    public function enqueueStyle(string $handle, string $url, array $deps, string $version): void {
        $this->styles[$handle] = compact('url', 'deps', 'version');
    }

    public function addInlineScript(string $handle, string $code, string $position): void {
        if (isset($this->inlineScripts[$handle])) {
            $this->inlineScripts[$handle]['code'] .= "\n" . $code;
        } else {
            $this->inlineScripts[$handle] = compact('code', 'position');
        }
    }
}