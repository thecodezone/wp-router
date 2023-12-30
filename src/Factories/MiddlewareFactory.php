<?php

namespace CodeZone\Router\Factories;

use CodeZone\Router\Middleware\Middleware;
use Illuminate\Container\Container;

/**
 * The MiddlewareFactory class is responsible for creating middleware instances.
 */
class MiddlewareFactory
{
	/**
	 * @var Container The object responsible for managing dependencies and creating instances of classes.
	 */
    protected Container $container;

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

	/**
	 * make method.
	 *
	 * Creates a new instance of the Middleware class.
	 *
	 * @param mixed $value The value to be resolved as a Middleware instance.
	 *
	 * @return Middleware The resolved Middleware instance.
	 */
    public function make($value): Middleware
    {
        if (is_object($value) && $value instanceof Middleware) {
            return $value;
        }

        return $this->container->make($value);
    }
}
