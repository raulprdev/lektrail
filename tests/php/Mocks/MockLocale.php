<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\Locale;

class MockLocale implements Locale
{
    public string $code = 'en_US';

    public function getCode(): string
    {
        return $this->code;
    }
}
