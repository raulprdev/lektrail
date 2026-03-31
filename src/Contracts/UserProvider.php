<?php

namespace LekTrail\Contracts;

interface UserProvider
{
    public function getCurrentUserId(): int;
    public function isLoggedIn(): bool;
}
