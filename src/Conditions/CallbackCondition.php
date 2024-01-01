<?php

namespace CodeZone\Router\Conditions;

/**
 * A condition that uses a callback function to evaluate its test.
 * Implements the Condition interface.
 */
class CallbackCondition implements Condition
{

    /**
     * Holds a reference to a callback function or method.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Class constructor.
     *
     * @param callable $callback The callback function to be assigned.
     *
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Executes the callback function and returns the result.
     *
     * @return bool The result of executing the callback function.
     */
    public function test(): bool
    {
        $callback = $this->callback;

        return $callback();
    }
}
