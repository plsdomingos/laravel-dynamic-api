<?php

namespace LaravelDynamicApi\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface AuthUser
{
    /**
     * Return user scopes.
     *
     * @return array
     */
    public function scopes(): array;

    /**
     * Return user scopes.
     *
     * @return array
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
    public function containsProfile(array $profiles): bool;

    /**
     * Check if the user has permission for the specific model.
     */
    public function checkProfileModel(string $type, string $profile, $request, $model): bool;
}