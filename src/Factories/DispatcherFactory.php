<?php

namespace CodeZone\Router\Factories;

use CodeZone\Router\FastRoute\Data;
use CodeZone\Router\FastRoute\Dispatcher;
use CodeZone\Router\FastRoute\Routes;
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
