<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetHeaders implements Middleware {

	/**
	 * Handles a request and prepares the response headers.
	 *
	 * @param Request $request The request object.
	 * @param Response $response The response object.
	 * @param callable $next The next middleware or handler to invoke.
	 *
	 * @return mixed The response generated by the next middleware or handler.
	 */
	public function handle( Request $request, Response $response, $next ) {

		foreach ( $response->headers->all() as $key => $value ) {
			header( $key . ': ' . $value[0] );
		}

		if ( is_array( $response->getContent() ) ) {
			header( 'Content-Type: application/json' );
		}

		status_header($response->getStatusCode());

		return $next( $request, $response );
	}
}
