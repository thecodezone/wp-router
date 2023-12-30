<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface Middleware
 *
 * This interface defines the contract that all middleware classes must adhere to.
 */
interface Middleware
{
    /**
     * Handle the request and response.
     *
     * @param Request $request The incoming request.
     * @param Response $response The outgoing response.
     * @param callable $next The next middleware or handler.
     *
     * @return mixed The response after handling the request.
     */
    public function handle(Request $request, Response $response, callable $next);
}
