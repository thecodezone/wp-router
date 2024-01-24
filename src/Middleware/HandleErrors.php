<?php

namespace CodeZone\Router\Middleware;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_filter;

/**
 * Class HandleErrors
 *
 * This class handles errors by checking the response status code and displaying an error
 * message using the WordPress `wp_die` function if the status code corresponds to an error code.
 *
 * @implements Middleware
 */
class HandleErrors implements Middleware
{
    /**
     * @var array $statusCodes
     *
     * The variable to store an array of status codes.
     * This array can be used to map status codes to their corresponding messages or meanings.
     * Example usage:
     *   $statusCodes = [
     *       404 => 'Not Found',
     *       500 => 'Internal Server Error',
     *   ];
     *
     * Note: It is recommended to use HTTP status codes (e.g., 200, 404) as keys
     * and their respective meanings or messages (e.g., 'OK', 'Not Found') as values.
     * However, you are not limited to using only HTTP status codes.
     */
    protected array $statusCodes = [];

    /**
     * Initializes a new instance of the class.
     *
     * This method sets the status codes using the filters applied to the 'codezone/router/error-codes' hook.
     * The status codes are filtered from the Response::$statusTexts array, with only codes equal to or greater than 400 included.
     *
     * @return void
     */
    public function __construct()
    {
        $this->statusCodes = apply_filters('codezone/router/error-codes', array_filter(Response::$statusTexts, function ($code) {
            return $code >= 400;
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Handle the request and response.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param callable $next The next middleware or request handler.
     *
     * @return mixed The result from the next middleware or request handler.
     * @throws Exception If an error occurs during handling.
     */
    public function handle(Request $request, Response $response, $next)
    {
        if (! $this->shouldHandle($request, $response)) {
            return $next($request, $response);
        }

        wp_die($this->statusCodes[ $response->getStatusCode() ], $response->getStatusCode(), [
            'response'  => $response->getContent(),
            'back_link' => true
        ]);

        return $next($request, $response);
    }

    /**
     * Determines whether the given response should be handled.
     *
     * This method checks various conditions to determine if the response should be handled or not.
     * The method returns true if any of the following conditions are met:
     *   - The response is an instance of JsonResponse.
     *   - The response's Content-Type header is set to "application/json".
     *   - The response's content is an array.
     *   - The response's status code exists in the $statusCodes property.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     *
     * @return bool Returns true if the response should be handled, otherwise false.
     */
    protected function shouldHandle(Request $request, Response $response): bool
    {
        if ($response->headers->get('Content-Type') === "application/json") {
            return false;
        }

        if (is_array($response->getContent())) {
            return false;
        }

        return array_key_exists($response->getStatusCode(), $this->statusCodes);
    }
}
