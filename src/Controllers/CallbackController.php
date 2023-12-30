<?php

namespace CodeZone\Router\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CallbackController
{
    public function handle(Request $request, Response $response, callable $handler): Response
    {
        return $handler($request, $response);
    }
}
