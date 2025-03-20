<?php

namespace LaravelDynamicApi\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface AuthenticateUser
{
    /**
     * Return user scopes.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Check if the user is admin.
     */
    public function isAdmin(): bool;

    /**
     * Check if the user is super admin.
     */
    public function isSuperAdmin(): bool;

    /**
     * Check if the user contains the profiles.
     */
    public function containsRoles(array $roles): bool;

    /**
     * Check if the user is soa admin.
     */
    public function updateLastLogin(): void;
}