<?php

namespace LekTrail\Contracts;

interface NonceVerifier
{
    public function verify(string $nonce, string $action): bool;
}
