<?php

namespace CodeZone\Router\Factories;

use CodeZone\Router\Factories\Middleware\UserHasCapFactory;
use CodeZone\Router\Middleware\Middleware;
use CodeZone\Router\Middleware\UserHasCap;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

/**
 * The MiddlewareFactory class is responsible for creating middleware instances.
 */
class MiddlewareFactory implements Factory
{
    /**
     * @var Container The object responsible for managing dependencies and creating instances of classes.
     */
    public Container $container;

    protected array $factories = [
        UserHasCap::class => UserHasCapFactory::class
    ];

    /**
     * __construct method.
     *
     * Initializes a new instance of the class.
     *
     * @param Container $container The container dependency.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function makeFromString(string $value): Middleware
    {
        $registered = $this->getRegisteredMiddleware();
        $signature  = Str::after($value, ':');
        $value      = Str::before($value, ':');

        if (isset($registered[ $value ])) {
            $className = $registered[ $value ];
        } else {
            $className = $value;
        }

        // This filter allows you to add a custom condition resolver.
        $condition = apply_filters('codezone/router/middleware/factory', null, $value, $className, $signature);

        if ($condition) {
            return $condition;
        }

        // Or you can add a custom condition factory to resolve the middleware.
        // It should implement the Conditions\Middleware.
        $factories = apply_filters('codezone/router/middleware/factories', $this->factories);
        $factory   = $factories[ $className ] ?? null;

        if ($factory) {
            return $this->container->makeWith($factory)->make($signature);
        }

        return $this->container->makeWith($className);
    }

    /**
     * Retrieves the registered middleware.
     *
     * This method returns an array of the registered conditions by applying the 'codezone/router/middleware'
     * filter to get the conditions from the filter hook. The returned array contains all the registered middleware.
     *
     * @return array An array of the registered middleware.
     */
    public function getRegisteredMiddleware(): array
    {
        return apply_filters('codezone/router/middleware', []);
    }

    /**
     * make method.
     *
     * Creates a new instance of the Middleware class.
     *
     * @param mixed $value The value to be resolved as a Middleware instance.
     *
     * @return Middleware The resolved Middleware instance.
     * @throws BindingResolutionException
     */
    public function make($value = null, $options = []): Middleware
    {
        if (is_object($value) && $value instanceof Middleware) {
            return $value;
        }

        return $this->makeFromString($value);
    }
}
