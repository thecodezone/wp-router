<?php

namespace Tests;

use CZ\Router\Middleware\HandleErrors;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CZ\Router\container;

class HandleErrorsMiddlewareTest extends TestCase {
	use HasRouter;

	/**
	 * @test
	 */
	public function it_handles_errors () {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);
		$middleware = container()->make(HandleErrors::class);
		$response->setStatusCode(404);

		$this->expectException(\Exception::class);

		$middleware->handle($request, $response, function ($request, $response) {
			$this->assertEquals(404, $response->getStatusCode());
		});
	}

	/**
	 * @test
	 */
	public function it_allows_success () {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);
		$middleware = container()->make(HandleErrors::class);
		$response->setStatusCode(200);

		$middleware->handle($request, $response, function ($request, $response) {
			$this->assertEquals(200, $response->getStatusCode());
		});
	}

}