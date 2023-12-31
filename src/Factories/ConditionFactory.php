<?php

namespace CodeZone\Router\Factories;

use ArrayAccess;
use CodeZone\Router\Conditions\CallbackCondition;
use CodeZone\Router\Conditions\Condition;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;

/**
 * The ConditionFactory class is responsible for creating Condition objects.
 */
class ConditionFactory
{
    /**
     * @var Container $container The global container object that holds instances of classes and provides dependency injection.
     */
    protected Container $container;

    /**
     * Constructs a new instance of the class.
     *
     * @param Container $container The container object to be injected.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function makeCondition($value): Condition
    {
        $registered = apply_filters('codezone/router/conditions', []);
        if (isset($registered[ $value ])) {
            $value = $registered[ $value ];
        }

        return $this->container->make($value);
    }

    /**
     * Creates a new condition object.
     *
     * @param mixed $condition The condition to create the object from.
     *
     * @return Condition|Collection The newly created condition object or collection of condition objects.
     */
    public function make($condition): Condition|Collection
    {
        if (! $condition) {
            return new CallbackCondition(function () {
                return true;
            });
        }

        if ($condition instanceof Condition) {
            return $condition;
        }

        if (is_callable($condition)) {
            return new CallbackCondition($condition);
        }

        if (is_string($condition)) {
            return $this->makeCondition($condition);
        }

        if (is_array($condition) || $condition instanceof ArrayAccess) {
            return Collection::make($condition)->each(function ($condition) {
                $this->make($condition);
            });
        }

        return new CallbackCondition(function () {
            return false;
        });
    }
}
