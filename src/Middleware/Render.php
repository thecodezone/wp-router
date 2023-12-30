<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        if ($response->getStatusCode() === 200) {
            if (is_array($response->getContent())) {
				$this->renderJson($response);
            } else {
				$this->render($response);
	        }
        }

        return $next($request, $response);
    }

	/**
	 * Renders the given response.
	 *
	 * @param Response $response The response to be rendered.
	 *
	 * @return void
	 */
	protected function render(Response $response): void {
		$hasHandler = has_action('codezone/router/render');
		if ($hasHandler) {
			do_action('codezone/router/render', $response);
			return;
		}
		echo $response->getContent();
	}

	/**
	 * Renders the given response as JSON.
	 *
	 * @param Response $response The response to be rendered.
	 *
	 * @return void
	 */
	protected function renderJson(Response $response): void {
		$hasHandler = has_action('codezone/router/render/json');
		if ($hasHandler) {
			do_action('codezone/router/render/json', $response);
			return;
		}
		echo wp_json_encode($response->getContent());
	}
}
