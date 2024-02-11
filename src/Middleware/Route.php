<?php

namespace CodeZone\Router\Middleware;

use CodeZone\Router\FastRoute\Routes;
use CodeZone\Router\Router;
use FastRoute;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function CodeZone\Router\collect;
use function CodeZone\Router\container;
use function CodeZone\Router\namespace_string;

/**
 * Represents a route in an application.
 *
 * This class implements the Middleware interface, allowing it to be used as a middleware in a middleware stack.
 */
class Route implements Middleware
{
    /**
     * @var mixed|null $handler Used for testing, but could also be used to call a custom handler rather than
     *                          relying on a WordPress filter.
     */
    protected $handler = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param callable|null $handler The callable handler (optional).
     *
     * @return void
     */
    public function __construct(callable|null $handler = null)
    {
        if ($handler) {
            $this->handler = $handler;
        }
    }

    /**
     * Handles the request by matching it with the appropriate route and applying the route's data to the request.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param callable $next The next middleware or request handler.
     *
     * @return bool|mixed Returns false if no route is found, otherwise returns the result of calling
     *                    the next middleware or request handler.
     *
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Response $response, $next)
    {
        $http_method         = $request->getMethod();
        $uri                 = $request->getRequestUri();
        $routable_param_keys = apply_filters(namespace_string('routable_params'), [
            'page',
            'action',
            'tab'
        ]) ?? [];
        $routable_params     = collect($request->query->all())->only($routable_param_keys);

        // Strip query string (?foo=bar) and decode URI
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        //Allow for including certain params in the route,
        //Like page=general
        //or action=save
        //or tab=general
        if (count($routable_params)) {
            $uri = $uri . '?' . http_build_query($routable_params->toArray());
        }

        $uri = trim(rawurldecode($uri), '/');

        //Get the matching route data from the router
        $dispatcher = container()->make(Router::class)->routes(function (Routes $r) {
            if ($this->handler) {
                $handler = $this->handler;
                $handler($r);
            }
            apply_filters(namespace_string('routes'), $r);
        });

        $matches = apply_filters(
            namespace_string('matched_routes'),
            $dispatcher->dispatch($http_method, $uri)
        );

        if (! $matches
             || $matches[0] === FastRoute\Dispatcher::NOT_FOUND
             || $matches[0] === FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            return false;
        }

        //Apply the matching route data to the request
        $request->routes = $matches;

        return $next($request, $response);
    }
}
