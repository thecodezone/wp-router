<?php

namespace CodeZone\Router\Middleware;

use CodeZone\Router\Factories\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function CodeZone\Router\container;

/**
 * Class Dispatch
 *
 * Connects the request to the appropriate controller method.
 * Apply any route-specific middleware.
 *
 * @see https://nikic.github.io/fast-route/
 */
class DispatchController implements Middleware
{
    protected $response_factory;

    /**
     * Class constructor
     *
     * @param ResponseFactory $response_factory The response factory object used to create responses
     *
     * @return void
     */
    public function __construct(ResponseFactory $response_factory)
    {
        $this->response_factory = $response_factory;
    }

    /**
     * Handles the incoming request and returns the response.
     *
     * @param Request $request The incoming request object
     * @param Response $response The response object to be modified
     * @param mixed $next The next middleware or handler in the chain
     *
     * @return mixed Returns the modified response object
     */
    public function handle(Request $request, Response $response, $next)
    {
        $route_info = $request->routes;

        if (! $route_info) {
            return $next($request);
        }

        [ $is_match, $handler, $vars ] = $route_info;

        if (! $is_match) {
            return $next($request);
        }

        [ $class, $method, $config ] = $handler;

        $middleware = $config['middleware'] ?? [];

        $response_before_controller = $response;

        //Apply route-specific middleware
        if (! empty($middleware)) {
            $response = container()->make(Stack::class)
                                   ->push(...$middleware)
                                   ->push(HandleRedirects::class)
                                   ->push(HandleErrors::class)
                                   ->run($request, $response);
        }

        //Call the  controller method
        $response = $this->response_factory->make(
            container()->call([ container()->make($class), $method ], [
                ...$vars,
                $request,
                $response,
            ]),
            [
                'response' => $response_before_controller
            ]
        );

        return $next($request, $response);
    }
}
