<?php

namespace CodeZone\Router\Conditions;

use Illuminate\Support\Collection;
use WP_User;
use function collect;

/**
 * Represents a condition based on user capabilities.
 *
 * This class implements the Condition interface.
 * It allows checking if the current user has all the required capabilities.
 */
class HasCap implements Condition
{
    /**
     * @var WP_User
     */
    protected WP_User $user;
    /**
     * @var array
     */
    protected array $capabilities = [];

    /**
     * Constructs a new instance of the class.
     *
     * @param string|iterable $capabilities The capabilities to assign.
     *     It can be either a string or an iterable containing multiple capabilities.
     *
     * @return void
     */
    public function __construct(string|iterable $capabilities, WP_User|null $user = null)
    {
        $this->setUser($user);

        if (is_string($capabilities)) {
            if ($capabilities) {
                $this->capabilities[] = $capabilities;
            }
        } else {
            array_push($this->capabilities, ...$capabilities);
        }
    }

    protected function setUser(WP_User|null $user): void
    {
        $this->user = $user ?? wp_get_current_user();
    }

    /**
     * Determines if the current user has all the required capabilities.
     *
     * This method checks if the current user has all the capabilities retrieved from the
     * `getCapabilities()` method. It iterates over each capability and checks if the
     * current user can perform that capability. If any capability check fails, this method
     * returns false. Otherwise, it returns true, indicating that the user has all the
     * required capabilities.
     *
     * @return bool Returns true if the current user has all the required capabilities,
     *              otherwise false.
     */
    public function test(): bool
    {
        $capabilities = $this->getCapabilities();

        $failure = $capabilities->first(function ($capability) {
            return ! $this->userHasCap($capability);
        });

        return ! $failure;
    }

    /**
     * Retrieves the capabilities as a collection.
     *
     * @return Collection The collection of capabilities.
     */
    protected function getCapabilities(): Collection
    {
        return collect($this->capabilities);
    }

    /**
     * Determines if the current user has a specific capability.
     *
     * This method checks if the current user has a specific capability by delegating the
     * check to the `has_cap` method of the user object. It passes the provided capability
     * and arguments to the `has_cap` method and returns the result.
     *
     * @param string $capability The capability to check.
     * @param array $args Optional. Additional arguments to pass to the `has_cap` method.
     *                           Default is an empty array.
     *
     * @return bool Returns true if the current user has the specified capability,
     *              otherwise false.
     */
    public function userHasCap($capability, $args = []): bool
    {
        return $this->user->has_cap($capability, ...$args);
    }
}
