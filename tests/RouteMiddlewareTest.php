<?php

namespace Tests;

use CZ\Router\Middleware\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CZ\Router\container;

class RouteMiddlewareTest extends TestCase {
	use HasRouter;

	/**
	 * @test
	 */
	public function it_returns_without_a_match() {
		$this->router();
		// Illuminate\Http\Request
		$request    = container()->make( Request::class );
		$response   = container()->make( Response::class );
		$middleware = container()->make( Route::class );
		$response   = $middleware->handle( $request, $response, function () {} );
		$this->assertFalse( $response );
	}

	/**
	 * @test
	 */
	public function it_finds_matches() {
		$this->router();
		// Illuminate\Http\Request
		$request    = container()->make( Request::class );
		$response   = container()->make( Response::class );
		$middleware = container()->makeWith( Route::class, [
			'handler' => function ( $r ) {
				$r->get( '', function () {
					return 'hello';
				} );
			},
		] );
		$middleware->handle( $request, $response, function ($request, $response) {
			$this->assertNotNull( $request->routes );
		} );
	}
}