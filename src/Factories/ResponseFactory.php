<?php

namespace CodeZone\Router\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use WP_Error;

/**
 * Class ResponseFactory
 *
 * The ResponseFactory class is responsible for creating response instances based on the given value.
 */
class ResponseFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create a new response instance from the given value.
     *
     * @param null $value
     * @param Response|null $response
     *
     * @return Response
     * @throws BindingResolutionException
     */
    public function make($value = null, BaseResponse|null $response = null): BaseResponse
    {
        return apply_filters('codezone/router/response', $this->mapResponse($value, $response));
    }

    /**
     * Map the given value to a response.
     *
     * @param null $value
     * @param Response|null $response
     *
     * @return Response
     * @throws BindingResolutionException
     */
    private function mapResponse($value = null, BaseResponse|null $response = null): BaseResponse
    {
        if ($value instanceof BaseResponse) {
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
