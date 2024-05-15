<?php

namespace CodeZone\Router\Factories\Middleware;

use CodeZone\Router\Factories\Factory;
use CodeZone\Router\Middleware\Middleware;
use CodeZone\Router\Middleware\UserHasCap;
use Illuminate\Container\Container;
use WP_User;

/**
 * Class UserHasCapFactory
 *
 * This class is responsible for creating instances of the UserHasCap middleware.
 * It implements the Factory interface.
 */
class UserHasCapFactory implements Factory
{
    /**
     * @var Container $container The container instance.
     */
    public Container $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Make a new UserHasCap middleware instance.
     *
     * @param mixed $value
     * @param iterable $options
     *
     * @return Middleware
     */
    public function make(mixed $value = null, iterable $options = [])
    {
        if ($value instanceof Middleware) {
            return $value;
        }

        $capabilities = false;
        $user         = $options['user'] ?? $this->getCurrentUser();

        if (is_array($value)) {
            $capabilities = $value;
        }

        if (is_string($value)) {
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
    protected function getCurrentUser()
    {
        return wp_get_current_user();
    }
}
