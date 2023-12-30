<?php

namespace CodeZone\Router\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class RedirectResponseFactory
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function make(string|Response $urlOrResponse, int $status = 302, iterable $headers = []): RedirectResponse
    {
        if ($urlOrResponse instanceof Response) {
            return $this->makeFromResponse($urlOrResponse);
        }

        $url = $urlOrResponse;

        return $this->container->makeWith(RedirectResponse::class, [
            'url'     => $url,
            'status'  => $status,
            'headers' => $headers
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function makeFromResponse(Response $request): RedirectResponse
    {
        $allowedStatuses = [ 301, 302, 303, 307, 308 ];
        $status          = in_array($request->getStatusCode(), $allowedStatuses) ? $request->getStatusCode() : 302;

        return $this->container->makeWith(RedirectResponse::class, [
            'url'     => $request->headers->get('Location'),
            'status'  => $status,
            'headers' => $request->headers->all()
        ]);
    }
}
