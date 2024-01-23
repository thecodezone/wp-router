<?php

namespace CodeZone\Router\Middleware;

use CodeZone\Router\Factories\MiddlewareFactory;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use function CodeZone\Router\container;

/**
 * Class Stack
 *
 * Represents a stack data structure that extends the Collection class.
 *
 * This class provides methods to handle a stack of middleware in the following order:
 * 1. Check if the stack is empty. If it is, return the provided request.
 * 2. Get the first middleware from the stack and resolve it if it's a string.
 * 3. If the middleware is not found, return a Collection containing the request and response.
 * 4. If the middleware is not an instance of Middleware, throw an exception.
 * 5. Call the `handle()` method of the middleware with the request, response, and a callback function.
 * 6. The callback function removes the first middleware from the stack and calls the `next()` method recursively.
 *
 * @extends Collection
 */
class Stack extends Collection
{
    /**
     * Runs the next middleware in the pipeline with the given request and response.
     *
     * @param $request The HTTP request object. If not provided, a new instance of the Request class will be created.
     * @param $response The HTTP response object. If not provided, a new instance of the Response class will be created.
     *
     * @return Response|null|string|Collection The result of class SetHeaders implements Middleware
     * {
     *
     * public function handle(Request $request, Response $response, $next)
     * {
     *
     * foreach ($response->headers->all() as $key => $value) {
     * header($key . ': ' . $value[0]);
     * }
     *
     * if (is_array($response->getContent())) {
     * header('Content-Type: application/json');
     * }
     *
     * return $next($request, $response);
     * }
     * }
     * the next middleware in the pipeline.
     * @throws BindingResolutionException If there is an error resolving the Request
     *                                    or Response class from the container.
     */
    public function run($request = null, $response = null)
    {
        return $this->next(
            $request ? $request : container()->make(Request::class),
            $response ? $response : container()->make(Response::class)
        );
    }

    /**
     * Calls the next middleware in the pipeline with the provided request and response objects.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     *
     * @return mixed The result of the next middleware in the pipeline.
     * @throws Exception If the first middleware is not an instance of Middleware.
     * @throws BindingResolutionException If there is an error resolving the middleware class from the container.
     */
    protected function next(Request $request, BaseResponse $response)
    {
        if ($this->isEmpty()) {
            return $response;
        }

        $middleware = $this->first();

        if (is_string($middleware)) {
            $middleware = container()->make(MiddlewareFactory::class)->make($middleware);
        }

        if (! $middleware) {
            return $response;
        }

        if (! $middleware instanceof Middleware) {
            throw( new Exception(__($this->first() . ' is not an instance of middleware', 'dt-plugin')) );
        }

        return $middleware->handle(
            $request,
            $response,
            $this->callback()
        );
    }

    /**
     * A callback method that returns a callable.
     *
     * @return callable A callable that takes in a Request object and a Response object as parameters and returns the result of the "next" method.
     */
    protected function callback(): callable
    {
        return function (Request $request, BaseResponse $response) {
            $this->shift();

            return $this->next($request, $response);
        };
    }
}
