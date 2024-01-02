<?php

namespace CodeZone\Router\Factories;

use ArrayAccess;
use CodeZone\Router\Conditions\CallbackCondition;
use CodeZone\Router\Conditions\Condition;
use CodeZone\Router\Conditions\HasCap;
use CodeZone\Router\Factories\Conditions\HasCapFactory;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The ConditionFactory class is responsible for creating Condition objects.
 */
class ConditionFactory implements Factory
{
    /**
     * @var Container $container The global container object that
     * holds instances of classes and provides dependency injection.
     */
    public Container $container;

    protected array $factories = [
        HasCap::class => HasCapFactory::class
    ];


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

    /**
     * Creates a new condition object.
     *
     * @param mixed $value The condition to create the object from.
     * @param iterable $options
     *
     * @return Condition The newly created condition object or collection of condition objects.
     * @throws BindingResolutionException
     */
    public function make(mixed $value = null, iterable $options = []): Condition
    {
        if (! $value) {
            return new CallbackCondition(function () {
                return true;
            });
        }

        if ($value instanceof Condition) {
            return $value;
        }

        if (is_callable($value)) {
            return new CallbackCondition($value);
        }

        if (is_string($value)) {
            return $this->makeFromString($value);
        }

        if (is_array($value) || $value instanceof ArrayAccess) {
            return Collection::make($value)->each(function ($value) {
                $this->make($value);
            });
        }

        return new CallbackCondition(function () {
            return false;
        });
    }

    /**
     * Makes a condition object based on the given value.
     *
     * @param mixed $name The value used to determine the condition object.
     *
     * @return Condition The condition object.
     * @throws BindingResolutionException
     */
    public function makeFromString(string $string): Condition
    {
        $registered = $this->getRegisteredConditions();
        $signature  = Str::after($string, ':');
        $name       = Str::before($string, ':');

        if (isset($registered[ $name ])) {
            $className = $registered[ $name ];
        } else {
            if ($this->container->has($name)) {
                $className = $name;
            } else {
                throw new BindingResolutionException("Condition {$name} is not registered.");
            }
        }

        // This filter allows you to add a custom condition resolver.

        $condition = apply_filters('codezone/router/conditions/factory', null, [
            'className' => $className,
            'name'      => $name,
            'signature' => $signature
        ]);
        if ($condition) {
            return $condition;
        }

        // Or you can add a custom condition factory to resolve the condition.
        // It should implement the Conditions\ConditionFactory.
        $factories = apply_filters('codezone/router/conditions/factories', $this->factories);
        $factory   = $factories[ $className ] ?? null;
        if ($factory) {
            return $this->container->make($factory)->make($signature);
        }

        return $this->container->makeWith($className);
    }

    /**
     * Retrieves the registered conditions.
     *
     * This method returns an array of the registered conditions by applying the 'codezone/router/conditions'
     * filter to get the conditions from the filter hook. The returned array contains all the registered conditions.
     *
     * @return array An array of the registered conditions.
     */
    public function getRegisteredConditions(): array
    {
        return apply_filters('codezone/router/conditions', []);
    }
}
