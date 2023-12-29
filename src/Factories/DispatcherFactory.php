<?php

namespace CZ\Router\Factories;

use CZ\Router\FastRoute\Data;
use CZ\Router\FastRoute\Dispatcher;
use CZ\Router\FastRoute\Routes;
use FastRoute\Dispatcher as BaseDispatcher;
use function FastRoute\simpleDispatcher;

class DispatcherFactory
{
    public function make(callable $callback, $options = []) : BaseDispatcher
    {
        return simpleDispatcher($callback, array_merge($options, [
            'routeCollector' => Routes::class,
            'dataGenerator'  => Data::class,
            'dispatcher'     => Dispatcher::class,
        ]));
    }
}
