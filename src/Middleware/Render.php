<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function CodeZone\Router\namespace_string;

/**
 * Class Render
 *
 * This class implements the Middleware interface and handles the rendering of responses.
 */
class Render implements Middleware
{

    /**
     * Handles the given request and response.
     *
     * @param Request $request The request object to handle.
     * @param Response $response The response object to handle.
     * @param mixed $next The callback function to be called next.
     *
     * @return mixed The result of the $next callback function.
     */
    public function handle(Request $request, Response $response, $next)
    {
        if ($response->headers->get('Content-Type') === 'application/json' || is_array($response->getContent())) {
            $this->renderJson($response);
        } else {
            $this->render($response);
        }

        return $next($request, $response);
    }

    /**
     * Renders the given response as JSON.
     *
     * @param Response $response The response to be rendered.
     *
     * @return void
     */
    protected function renderJson(Response $response): void
    {
        $hasHandler = has_action(namespace_string('render_json'));
        if ($hasHandler) {
            do_action(namespace_string('render_json'), $response);

            return;
        }
        $response->send();
    }

    /**
     * Renders the given response, but only if the response status code is 200.
     *
     * @param Response $response The response to be rendered.
     *
     * @return void
     */
    protected function render(Response $response): void
    {
        $hasHandler = has_action(namespace_string('render'));
        if ($hasHandler) {
            do_action(namespace_string('render'), $response);

            return;
        }
        if ($response->getStatusCode() === 200) {
            $response->send();
        }
    }
}
