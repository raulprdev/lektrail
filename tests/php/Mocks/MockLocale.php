<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\Locale;

class MockLocale implements Locale
{
    public string $code = 'en_US';

    public function getCode(): string
    {
        return $this->code;
    }
}
