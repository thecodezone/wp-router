<?php


namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Controller {
	public function request( Request $request, Response $response ): Response {
		$response->setContent( '<h1>Hello</h1>' );

		return $response;
	}

	public function withParam( Request $request, Response $response, $param ): Response {
		$response->setContent( '<h1>' . $param . '</h1>' );

		return $response;
	}

	public function returnsString( Request $request, Response $response ): string {
		return '<h1>Hello</h1>';
	}

	public function returnsArray( Request $request, Response $response ): array {
		return [
			'hello' => 'world'
		];
	}

	public function returnsArrayAsResponse( Request $request, Response $response ): Response {
		$response->setContent( [
			'hello' => 'world'
		] );

		return $response;
	}

	public function returnsError( Request $request, Response $response ): Response {
		$response->setStatusCode( 500 );

		return $response;
	}

	public function returnsHeader( Request $request, Response $response ): Response {
		$response->setHeader( 'X-Test', 'test' );

		return $response;
	}

	public function returnsRedirect( Request $request, Response $response ): Response {
		$response->setStatusCode( 302 );
		$response->setContent( 'https://google.com' );

		return $response;
	}
}
