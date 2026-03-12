<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Locale as LocaleInterface;

class Locale implements LocaleInterface {

    public function getCode(): string {
        return get_locale();
    }
}