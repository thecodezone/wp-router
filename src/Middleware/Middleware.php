<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface Middleware
{

    public function handle(Request $request, Response $response, callable $next);
}
