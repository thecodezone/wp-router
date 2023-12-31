<?php

namespace CodeZone\Router\Factories\Middleware;

use CodeZone\Router\Factories\Factory;
use CodeZone\Router\Middleware\Middleware;
use CodeZone\Router\Middleware\UserHasCap;
use WP_User;

class UserHasCapFactory implements Factory
{

    /**
     * Make a new UserHasCap middleware instance.
     *
     * @param mixed $value
     * @param iterable $options
     *
     * @return Middleware
     */
    public function make(mixed $value, iterable $options = [])
    {
        if ($value instanceof Middleware) {
            return $value;
        }

        $capabilities = false;
        $user         = $options['user'] ?? $this->getCurrentUser();

        if (is_array($value)) {
            $capabilities = $value;
        }

        if (! is_string($value)) {
            $capabilities = explode(',', $value);
        }

        return $this->container->makeWith(UserHasCap::class, [
            'capabilities' => $capabilities,
            'user'         => $user
        ]);
    }

    /**
     * Retrieves the currently logged-in user.
     *
     * Returns the current WordPress user as an instance of WP_User
     * or returns false if no user is logged in.
     *
     * @return WP_User|false The currently logged-in user, or false if no user is logged in.
     */
    protected function getCurrentUser(): WP_User|false
    {
        return wp_get_current_user();
    }
}