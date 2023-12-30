<?php

namespace CodeZone\Router\Middleware;

use CodeZone\Router\Conditions\HasCap;
use CodeZone\Router\Factories\RedirectResponseFactory;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function CodeZone\Router\container;

/**
 * Class HasCap
 *
 * Implements the Middleware interface and checks if a user has sufficient permissions.
 */
class UserHasCap implements Middleware
{
    /**
     * @var string|iterable $capabilities
     * The variable that holds the capabilities of the system.
     * It can either be a string or an iterable data structure.
     */
    protected string|iterable $capabilities = '';

    /**
     * @var string|null $redirect_to
     * The variable that holds the URL to redirect to.
     * It can either be a string representing a valid URL or null if no redirection is needed.
     */
    protected mixed $redirect_to;

    /**
     * __construct method.
     *
     * Constructs a new instance of the class.
     *
     * @param string|iterable $capabilities The capabilities parameter that determines the
     *                                      permissions or features associated with the object being constructed.
     * @param string|null $redirect_to The redirect_to parameter that specifies the target URL to redirect to after
     *                                completing the construction. It is set to null by default.
     */
    public function __construct(string|iterable $capabilities, string|null $redirect_to = null)
    {
        $this->capabilities = $capabilities;
        $this->redirect_to  = $redirect_to;
    }

    /**
     * handle method.
     *
     * Handles the request and response objects by checking if the user has sufficient permissions.
     *
     * @param Request $request The request object that contains the incoming HTTP request data.
     * @param Response $response The response object that is used to send the HTTP response.
     * @param callable $next The next callable in the middleware pipeline.
     *
     * @return mixed The result of calling the $next callable with the $request and $response objects.
     * @throws Exception If the handleInsufficientPermissions method fails to handle insufficient
     *                   permissions.
     *
     */
    public function handle(Request $request, Response $response, callable $next)
    {
        $can = container()->makeWith(HasCap::class, [
            'capabilities' => $this->capabilities
        ])->test();

        if (! $can) {
            $response = $this->handleInsufficientPermissions($request, $response);
        }

        return $next($request, $response);
    }

    /**
     * handleInsufficientPermissions method.
     *
     * Handles the case when the user has insufficient permissions to access a specific resource.
     *
     * @param Request $request The request object representing the incoming HTTP request.
     * @param Response $response The response object representing the outgoing HTTP response.
     *
     * @return Response The response object after handling the insufficient permissions.
     *                  It can either be a redirect response or an abort response.
     */
    protected function handleInsufficientPermissions(Request $request, Response $response): Response
    {
        if ($this->redirect_to) {
            $response = $this->redirect($request, $response);
        } else {
            $response = $this->abort($request, $response);
        }

        return $response;
    }

    /**
     * redirect method.
     *
     * Redirects the request to a specified URL.
     *
     * @param Request $request The request object that represents the incoming HTTP request.
     * @param Response $response The response object that represents the HTTP response to be sent back to the client.
     *
     * @return RedirectResponse The redirect response object.
     */
    protected function redirect(Request $request, Response $response)
    {
        $response->headers->set('Location', $this->redirect_to);

        return container()->make(RedirectResponseFactory::class)->make($response);
    }

    /**
     * abort method.
     *
     * Aborts the current HTTP request and returns a response with a 403 status code and an error message.
     *
     * @param Request $request The request object representing the current HTTP request.
     * @param Response $response The response object that will be sent back to the client.
     *
     * @return Response The response object with a 403 status code and the error message set as its content.
     */
    protected function abort(Request $request, Response $response): Response
    {
        $response->setStatusCode(403);
        $response->setContent($this->getErrorMessage());

        return $response;
    }

    /**
     * getErrorMessage method.
     *
     * Retrieves the error message indicating insufficient access.
     *
     * @return string|null The error message indicating insufficient access.
     *                     Returns null if the message could not be retrieved.
     */
    protected function getErrorMessage(): ?string
    {
        return __('You do not have sufficient access.', 'dt-plugin');
    }
}
