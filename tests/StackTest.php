<?php

namespace Tests;

use CodeZone\Router\Middleware\Render;
use CodeZone\Router\Middleware\SetHeaders;
use CodeZone\Router\Middleware\Stack;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CodeZone\Router\container;

class StackTest  extends TestCase {
	use HasRouter;

	/**
	 * @test
	 */
	public function it_calls_consecutive_middleware () {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);

		$response->setStatusCode(302);

		$mock = $this->getMockBuilder( Render::class )
			->getMock();
		$mock->expects( $this->once() )
			->method( 'handle' )
			->with( $request, $response, function($request, $response) {
				return $response;
			})
		    ->willReturn( $response );

		$stack = container()->make( Stack::class );
		$stack->push(SetHeaders::class);
		$stack->push($mock);
		$response = $stack->run($request, $response);

		$this->assertEquals(302, $response->getStatusCode());
	}
}