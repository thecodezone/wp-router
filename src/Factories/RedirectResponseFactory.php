<?php

namespace CodeZone\Router\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RedirectResponseFactory implements Factory
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function make($value = null, iterable $options = []): RedirectResponse
    {
        $status  = $options['status'] ?? 302;
        $headers = $options['headers'] ?? [];

        if ($value instanceof RedirectResponse) {
            return $value;
        }

        if ($value instanceof BaseResponse) {
            return $this->makeFromResponse($value);
        }

        $url = $value;

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
