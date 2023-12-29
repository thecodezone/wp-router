<?php

namespace CZ\Router\Middleware;

use CZ\Router\FastRoute\Routes;
use CZ\Router\Router;
use FastRoute;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CZ\Router\container;

/**
 * Route Middleware
 *
 * @see https://github.com/nikic/FastRoute
 */
class Route implements Middleware
{
    protected $handler = null;

    public function __construct(callable|null $handler = null)
    {
        if ($handler) {
            $this->handler = $handler;
        }
    }

    public function handle(Request $request, Response $response, $next)
    {
        $http_method         = $request->getMethod();
        $uri                 = $request->getRequestUri();
        $routable_param_keys = apply_filters('cz/router/routable_params', [ 'page', 'action', 'tab' ]) ?? [];
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
            apply_filters('cz/router/routes', $r);
        });

        $matches = apply_filters(
            'cz/router/matched_routes',
            $dispatcher->dispatch($http_method, $uri)
        );

        if (! $matches || $matches[0] === FastRoute\Dispatcher::NOT_FOUND) {
            return false;
        }

        //Apply the matching route data to the request
        $request->routes = $matches;

        return $next($request, $response);
    }
}
