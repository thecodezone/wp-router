<?php

namespace CodeZone\Router\Factories;

use CodeZone\Router\FastRoute\Data;
use CodeZone\Router\FastRoute\Dispatcher;
use CodeZone\Router\FastRoute\Routes;
use FastRoute\Dispatcher as BaseDispatcher;
use function FastRoute\simpleDispatcher;

/**
 * Creates a new instance of a dispatcher configured with the provided callback and options.
 *
 * @param callable $callback The callback function that will be used by the dispatcher.
 * @param array $options Additional options for configuring the dispatcher.
 *
 * @return BaseDispatcher An instance of the dispatcher.
 */
class DispatcherFactory implements Factory
{
    /**
     * Creates a new instance of the base dispatcher.
     *
     * @param mixed $value The callback function to be used by the dispatcher.
     * @param array $options [optional] Additional options for the dispatcher. Default is an empty array.
     *
     * @return BaseDispatcher An instance of the base dispatcher.
     */
    public function make($value = null, $options = []): BaseDispatcher
    {
        return simpleDispatcher($value, array_merge($options, [
            'routeCollector' => Routes::class,
            'dataGenerator'  => Data::class,
            'dispatcher'     => Dispatcher::class,
        ]));
    }
}
