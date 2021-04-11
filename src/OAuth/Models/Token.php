<?php

declare(strict_types=1);

namespace CQ\OAuth\Models;

final class Token
{
    public function __construct(
        private string $accessToken,
        private string $refreshToken,
        private int $expiresAt
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function hasExpired() : bool
    {
        return $this->expiresAt > time();
    }
}
