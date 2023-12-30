<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class HandleErrors
 *
 * This class handles errors by checking the response status code and displaying an error
 * message using the WordPress `wp_die` function if the status code corresponds to an error code.
 *
 * @implements Middleware
 */
class HandleErrors implements Middleware
{

	/**
	 * Handle the request and response.
	 *
	 * @param Request $request The HTTP request object.
	 * @param Response $response The HTTP response object.
	 * @param callable $next The next middleware or request handler.
	 *
	 * @return mixed The result from the next middleware or request handler.
	 * @throws \Exception If an error occurs during handling.
	 */
    public function handle(Request $request, Response $response, $next)
    {
        $error_codes = apply_filters('codezone/router/error-codes', [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ]);

        if (array_key_exists($response->getStatusCode(), $error_codes)) {
            wp_die($error_codes[ $response->getStatusCode() ], $response->getStatusCode(), [
                'response'  => $response->getContent(),
                'back_link' => true,
            ]);
        }

        return $next($request, $response);
    }
}
