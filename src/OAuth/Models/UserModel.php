<?php

declare(strict_types=1);

namespace CQ\OAuth\Models;

final class User
{
    public function __construct(
        private bool $allowed,
        private string $id,
        private string $email,
        private bool $emailVerified,
        private array $roles
    ) {
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array(
            needle: $role,
            haystack: $this->roles,
            strict: true
        );
    }
}
