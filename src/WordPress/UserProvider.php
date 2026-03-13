<?php

namespace Completionist\WordPress;

use Completionist\Contracts\UserProvider as UserProviderContract;

class UserProvider implements UserProviderContract {

    public function getCurrentUserId(): int {
        return get_current_user_id();
    }

    public function isLoggedIn(): bool {
        return is_user_logged_in();
    }
}