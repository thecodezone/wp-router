<?php

namespace CZ\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SetHeaders implements Middleware
{

    public function handle(Request $request, Response $response, $next)
    {

        foreach ($response->get_headers() as $key => $value) {
            header($key . ': ' . $value);
        }

        if (is_array($response->get_data())) {
            header('Content-Type: application/json');
        }

        return $next($request, $response);
    }
}
