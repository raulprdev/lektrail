<?php

namespace Completionist\WordPress;

use Completionist\Contracts\NonceVerifier as NonceVerifierContract;

class NonceVerifier implements NonceVerifierContract
{
    public function verify(string $nonce, string $action): bool
    {
        return wp_verify_nonce($nonce, $action) !== false;
    }
}
