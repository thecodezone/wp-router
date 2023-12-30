<?php

namespace CodeZone\Router;

use CodeZone\Router\Factories\DispatcherFactory;
use FastRoute\Dispatcher;
use Illuminate\Container\Container;

/**
 * Router
 *
 * The Router class handles routing functionality within the application.
 *
 * @package CodeZone\Router
 */
class Router
{
	/**
	 * @var Container|mixed
	 */
    public Container $container;


	/**
	 * @var array
	 */
    public array $config;

	/**
	 * @var Router|null
	 */
    protected static ?Router $instance = null;

	/**
	 * @var DispatcherFactory
	 */
    protected DispatcherFactory $dispatcherFactory;

	/**
	 * @param array $config
	 * @param DispatcherFactory $dispatcherFactory
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
     * @return \CodeZone\Router\Router
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
     * @param \CodeZone\Router\DataObjects\Config $config
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
