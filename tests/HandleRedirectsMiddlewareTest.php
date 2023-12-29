<?php

namespace Tests;

use CZ\Router\Middleware\HandleRedirects;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CZ\Router\container;

class HandleRedirectsMiddlewareTest extends TestCase {
	use HasRouter;

	/**
	 * @test
	 */
	public function it_handles_url_redirects() {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);
		$response->setContent('https://google.com');
		$response->setStatusCode(301);

		$mock = $this->createPartialMock(HandleRedirects::class, ['redirect', 'exit']);

		$mock->expects($this->atLeastOnce())
			->method('redirect')
			->with('https://google.com', 301);

		$mock->handle($request, $response, function ($request, $response) {
			$this->assertEquals(301, $response->getStatusCode());
		});
	}

	/**
	 * @test
	 */
	public function it_handles_path_redirects() {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);
		$response->setContent('/page');
		$response->setStatusCode(301);

		$mock = $this->createPartialMock(HandleRedirects::class, ['redirect', 'exit' ]);

		$mock->expects($this->atLeastOnce())
		     ->method('redirect')
		     ->with('/page', 301);

		$mock->handle($request, $response, function ($request, $response) {
			$this->assertEquals(301, $response->getStatusCode());
		});
	}

	/**
	 * @test
	 */
	public function it_skips_non_paths() {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);
		$response->setContent('<p>This is a sentence.</p>');
		$response->setStatusCode(301);

		$mock = $this->createPartialMock(HandleRedirects::class, ['redirect', 'exit']);

		$mock->expects($this->atMost(0))
		     ->method('redirect');

		$mock->handle($request, $response, function ($request, $response) {
			$this->assertEquals(301, $response->getStatusCode());
		});
	}

}