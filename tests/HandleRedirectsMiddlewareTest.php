<?php

namespace Tests;

use CodeZone\Router\Middleware\HandleRedirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function CodeZone\Router\container;

class HandleRedirectsMiddlewareTest extends TestCase
{
    use HasRouter;

    /**
     * @test
     */
    public function it_handles_url_redirects()
    {
        $this->router();
        $request  = container()->make(Request::class);
        $response = $this->createPartialMock(RedirectResponse::class, [ 'send' ]);
        $response->expects($this->once())
                 ->method('send');
        $response->setStatusCode(301);

        $mock = $this->createPartialMock(HandleRedirects::class, [ 'exit' ]);
        $mock->expects($this->once())
             ->method('exit');

        $mock->handle($request, $response, function ($request, $response) {
            $this->assertEquals(301, $response->getStatusCode());
        });
    }
}
