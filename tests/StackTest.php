<?php

namespace Tests;

use CZ\Router\FastRoute\Routes;
use CZ\Router\Middleware\CanManageDT;
use CZ\Router\Middleware\DispatchController;
use CZ\Router\Middleware\Render;
use CZ\Router\Middleware\Stack;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CZ\Router\container;

class StackTest  extends TestCase {
	use HasRouter;

	/**
	 * @test
	 */
	public function it_calls_consecutive_middleware () {
		$this->router();
		$request = container()->make(Request::class);
		$response = container()->make(Response::class);

		$mock = $this->getMockBuilder( Render::class )
			->getMock();
		$mock->expects( $this->once() )
			->method( 'handle' )
			->with( $request, $response, function($request, $response) {
				return $response;
			})
		    ->willReturn( $response );

		$stack = container()->make( Stack::class );
		$stack->push(CanManageDT::class);
		$stack->push($mock);
		$response = $stack->run($request, $response);

		$this->assertEquals(302, $response->getStatusCode());
	}
}