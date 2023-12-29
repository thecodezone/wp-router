<?php

namespace Tests;

use CZ\Router\Controllers\CallbackController;
use CZ\Router\FastRoute\Routes;
use CZ\Router\Router;
use Illuminate\Container\Container;
use function CZ\Router\container;

final class RouterTest extends TestCase
{
	use HasRouter;

	/**
	 * @test
	 */
	public function it_registers(): void
	{
		$container = new Container();
		$router = Router::register([
			'container' => $container,
		]);
		$this->assertInstanceOf(Router::class, $router);
		$this->assertTrue($container->has(Router::class));
		$this->assertInstanceOf(Container::class, Router::instance()->container);
		$this->assertInstanceOf(Container::class, $container->make(Router::class )->container );
		$this->assertInstanceOf(Container::class, container());
	}

	/**
	 * @test
	 */
	public function it_registers_callback_routes(): void
	{
		$router = $this->router();
		$dispatcher = $router->routes(function(Routes $r) {
			$r->get('/', function() {
				return 'Hello World';
			});
		});
		$routes = $dispatcher->dispatch('GET', '/' );

		$this->assertEquals(
			CallbackController::class, $routes[1][0],
		);

		$this->assertEquals(
			'handle', $routes[1][1],
		);

		$this->assertEquals(
			'Hello World', $routes[1][2]['handler']()
		);
	}

	/**
	 * @test
	 */
	public function it_registers_string_routes(): void
	{
		$router = $this->router();
		$dispatcher = $router->routes(function(Routes $r) {
			$r->get('/', Controller::class . '@request');
		});
		$routes = $dispatcher->dispatch('GET', '/' );

		$this->assertEquals(
			Controller::class, $routes[1][0],
		);

		$this->assertEquals(
			'request', $routes[1][1],
		);
	}

	/**
	 * @test
	 */
	public function it_registers_array_routes(): void
	{
		$router = $this->router();
		$dispatcher = $router->routes(function(Routes $r) {
			$r->get('/', [Controller::class, 'request']);
		});
		$routes = $dispatcher->dispatch('GET', '/' );

		$this->assertEquals(
			Controller::class, $routes[1][0],
		);

		$this->assertEquals(
			'request', $routes[1][1],
		);
	}
}


