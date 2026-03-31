<?php

namespace LekTrail\WordPress;

use LekTrail\Contracts\Locale as LocaleInterface;

class Locale implements LocaleInterface
{
    public function getCode(): string
    {
        return get_locale();
    }
}
