<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\NonceVerifier;

class MockNonceVerifier implements NonceVerifier
{
    public bool $isValid = true;
    public ?string $lastNonce = null;
    public ?string $lastAction = null;

    public function verify(string $nonce, string $action): bool
    {
        $this->lastNonce = $nonce;
        $this->lastAction = $action;
        return $this->isValid;
    }
}
