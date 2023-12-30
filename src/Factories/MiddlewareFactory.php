<?php

namespace CodeZone\Router\Factories;

use CodeZone\Router\Middleware\Middleware;
use Illuminate\Container\Container;

class MiddlewareFactory
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function make($value): Middleware
    {
        if (is_object($value) && $value instanceof Middleware) {
            return $value;
        }

        return $this->container->make($value);
    }
}
