<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\UserProvider;

class MockUserProvider implements UserProvider {

    public int $userId = 0;

    public function getCurrentUserId(): int {
        return $this->userId;
    }

    public function isLoggedIn(): bool {
        return $this->userId > 0;
    }
}