<?php

namespace CodeZone\Router\Factories\Conditions;

use CodeZone\Router\Conditions\Condition;
use CodeZone\Router\Conditions\HasCap;
use CodeZone\Router\Factories\Factory;
use WP_User;

class HasCapFactory implements Factory
{
    protected $container;

    /**
     * HasCapFactory constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Make a HasCap condition instance
     *
     * @param $value
     * @param $options
     *
     * @return Condition
     */
    public function make($value, $options = []): Condition
    {
        if ($value instanceof Condition) {
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


        return $this->container->makeWith(HasCap::class, [
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
