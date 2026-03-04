<?php

namespace Completionist;

class WordPressScriptLoader implements ScriptLoader {

    public function enqueueScript(string $handle, string $url, array $deps, string $version, bool $inFooter): void {
        wp_enqueue_script($handle, $url, $deps, $version, $inFooter);
    }

    public function enqueueStyle(string $handle, string $url, array $deps, string $version): void {
        wp_enqueue_style($handle, $url, $deps, $version);
    }

    public function addInlineScript(string $handle, string $code, string $position): void {
        wp_add_inline_script($handle, $code, $position);
    }
}