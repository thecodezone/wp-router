<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HandleErrors implements Middleware
{

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
