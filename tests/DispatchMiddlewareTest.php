<?php

namespace Tests;

use CodeZone\Router\Middleware\DispatchController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CodeZone\Router\container;

class DispatchMiddlewareTest extends TestCase
{
    use HasRouter;

    /**
     * @test
     */
    public function it_dispatches_responses()
    {
        $this->router();
        $request = container()->make(Request::class);
        $response = container()->make(Response::class);
        $request->attributes->set('route_info', $this->router()->routes(function ($router) {
            $router->get('/', [Controller::class, 'request']);
        })->dispatch('GET', '/'));
        $middleware = container()->make(DispatchController::class);
        $middleware->handle($request, $response, function ($request, $response) {
            $this->assertStringContainsString('Hello', $response->getContent());
        });
    }

    /**
     * @test
     */
    public function it_dispatches_responses_with_params()
    {
        $this->router();
        $request = container()->make(Request::class);
        $response = container()->make(Response::class);
        $request->attributes->set('route_info', $this->router()->routes(function ($router) {
            $router->get('/{param}', [Controller::class, 'withParam']);
        })->dispatch('GET', '/hello'));
        $middleware = container()->make(DispatchController::class);
        $middleware->handle($request, $response, function ($request, $response) {
            $this->assertStringContainsString('hello', $response->getContent());
        });
    }

    /**
     * @test
     */
    public function it_dispatches_strings()
    {
        $this->router();
        $request = container()->make(Request::class);
        $response = container()->make(Response::class);
        $request->attributes->set('route_info', $this->router()->routes(function ($router) {
            $router->get('/', [Controller::class, 'returnsString']);
        })->dispatch('GET', '/'));
        $middleware = container()->make(DispatchController::class);
        $middleware->handle($request, $response, function ($request, $response) {
            $this->assertStringContainsString('Hello', $response->getContent());
        });
    }
}
