<?php

namespace CZ\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SetHeaders implements Middleware
{

    public function handle(Request $request, Response $response, $next)
    {

        foreach ($response->headers->all() as $key => $value) {
            header($key . ': ' . $value[0]);
        }

        if (is_array($response->getContent())) {
            header('Content-Type: application/json');
        }

        return $next($request, $response);
    }
}
