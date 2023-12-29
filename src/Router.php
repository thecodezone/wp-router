<?php

namespace CZ\Router;

use CZ\Router\Factories\DispatcherFactory;
use FastRoute\Dispatcher;
use Illuminate\Container\Container;

class Router
{
    public Container $container;
    public array $config;
    protected static ?Router $instance = null;
    protected DispatcherFactory $dispatcherFactory;

    /**
     * Router constructor.
     *
     * @param array $config
     * @param \CZ\Router\Factories\DispatcherFactory $dispatcherFactory
     *
     * @throws \Exception
     */
    public function __construct(array $config, DispatcherFactory $dispatcherFactory)
    {
        static::validateConfig($config);

        $this->config = $config;
        $this->container = $config['container'];
        $this->dispatcherFactory = $dispatcherFactory;
    }

    /**
     * Get the router instance
     *
     * @return \CZ\Router\Router
     * @throws \Exception
     */
    public static function instance() : Router
    {
        if (! static::$instance) {
            throw new \Exception('Router not registered.');
        }
        return static::$instance;
    }

    /**
     * Register the router with a container
     *
     * @param \CZ\Router\DataObjects\Config $config
     *
     * @throws \Exception
     */
    public static function register(array $config) : Router
    {
        static::validateConfig($config);

        $container = $config['container'];

        if (! $container->has(self::class)) {
            $container->singleton(self::class, function ($container) use ($config) {
                return new Router($config, $container->make(DispatcherFactory::class));
            });
        }

        $instance = $container->make(self::class);
        self::$instance = $instance;
        return $instance;
    }

    /**
     * Register routes via a callback
     *
     * @param callable $callback
     * @param array $options
     *
     * @return \FastRoute\Dispatcher
     */
    public function routes(callable $callback, array $options = []) : Dispatcher
    {
        return $this->dispatcherFactory->make($callback, $options);
    }

    /**
     * Validate the router config
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public static function validateConfig(array $config) : void
    {
        if (! $config['container'] instanceof Container) {
            throw new \Exception('Container must be an instance of Illuminate\Container\Container');
        }
    }
}
