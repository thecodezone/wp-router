<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CodeZone\Router\is_json;

/**
 * Class RedirectMiddleware
 *
 * This middleware is used to redirect the user to the URL specified in the response body.
 *
 * @package CodeZone\Router\Middleware
 */
class HandleRedirects implements Middleware
{

    /**
     * Handles the redirect.
     * If the response is a redirect, it will redirect the user to the URL specified in the response body.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @param $next
     *
     * @return mixed
     */
    public function handle(Request $request, Response $response, $next)
    {
        if ($response->getStatusCode() === 301 || $response->getStatusCode() === 302) {
            if (is_string($response->getContent()) && ! is_json($response->getContent())) {
                $url = filter_var($response->getContent(), FILTER_SANITIZE_URL);
                $parsed = parse_url($url);
                if (! isset($parsed['host']) && isset($parsed['path'])) {
                    $path = $parsed['path'];
                    if (preg_match('~^[a-zA-Z0-9/._-]*$~', $path)) {
                        $this->redirect($parsed['path'], $response->getStatusCode());
                    }
                } else {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $this->redirect($response->getContent(), $response->getStatusCode());
                    }
                }
            }
        }

        return $next($request, $response);
    }

    /**
     * Redirects the user to the URL specified in the response body.
     *
     * @param $url
     * @param $code
     *
     * @return void
     */
    public function redirect($to, $code): void
    {
        wp_redirect($to, $code);
        $this->exit();
    }

    /**
     * Exits the current script execution.
     *
     * @return void
     */
    protected function exit(): void
    {
        exit;
    }
}
