<?php

namespace CZ\Router\Factories;

use ArrayAccess;
use CZ\Router\Conditions\CallbackCondition;
use CZ\Router\Conditions\Condition;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;

class ConditionFactory
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
            return $this->container->make($condition);
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
