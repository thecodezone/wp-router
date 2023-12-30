<?php

namespace CodeZone\Router\Middleware;

use CodeZone\Router\Factories\RedirectResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RedirectMiddleware
 *
 * This middleware is used to redirect the user to the URL specified in the response body.
 *
 * @package CodeZone\Router\Middleware
 */
class HandleRedirects implements Middleware
{
    protected $redirectResponesFactory;

    public function __construct(RedirectResponseFactory $redirectResponseFactory)
    {
        $this->redirectResponesFactory = $redirectResponseFactory;
    }

    /**
     * Handles the redirect.
     * If the response is a redirect, it will redirect the user to the URL specified in the response body.
     *
     * @param Request $request
     * @param Response $response
     * @param $next
     *
     * @return mixed
     */
    public function handle(Request $request, Response $response, $next)
    {
        if ($response->getStatusCode() === 301 || $response->getStatusCode() === 302) {
            if (get_class($response) === 'Illuminate\Http\RedirectResponse') {
                $response = $this->redirectResponesFactory->make($response);
            }
            $response->send();
            $this->exit();
        }

        return $next($request, $response);
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
