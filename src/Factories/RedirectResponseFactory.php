<?php

namespace CodeZone\Router\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RedirectResponseFactory
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function make(string|BaseResponse $urlOrResponse, int $status = 302, iterable $headers = []): RedirectResponse
    {
        if ($urlOrResponse instanceof RedirectResponse) {
            return $urlOrResponse;
        }

        if ($urlOrResponse instanceof BaseResponse) {
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
    public function makeFromResponse(BaseResponse $request): RedirectResponse
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
