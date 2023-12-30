<?php

namespace CodeZone\Router\Factories;

use Illuminate\Container\Container;
use Illuminate\Http\Response;
use WP_Error;

/**
 * Class ResponseFactory
 */
class ResponseFactory
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create a new response instance from the given value.
     *
     * @param null $value
     * @param \Illuminate\Http\Response|null $response
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function make($value = null, Response|null $response = null): Response
    {
        return apply_filters('codezone/router/response', $this->mapResponse($value, $response));
    }

    /**
     * Map the given value to a response.
     *
     * @param null $value
     * @param \Illuminate\Http\Response|null $response
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function mapResponse($value = null, Response|null $response = null): Response
    {
        if ($value instanceof Response) {
            return $value;
        }

        if (! $response) {
            $response = $this->container->make(Response::class);
        }

        if (is_numeric($value) || is_string($value) || is_array($value)) {
            $response->setContent($value);
        }

        if ($value instanceof WP_Error) {
            $response->setStatusCode($value->get_error_code());
            $response->setContent($value->get_error_message());
        }

        return $response;
    }
}
