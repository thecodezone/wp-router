<?php

namespace Tests;

use CodeZone\Router\Conditions\HasCap;
use CodeZone\Router\Factories\ConditionFactory;
use CodeZone\Router\Factories\Conditions\HasCapFactory;
use CodeZone\Router\Factories\Middleware\UserHasCapFactory;
use CodeZone\Router\Factories\MiddlewareFactory;
use CodeZone\Router\Middleware\UserHasCap;
use Illuminate\Container\Container;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function CodeZone\Router\container;

class HasCapTest extends TestCase
{
    use HasRouter;

    /**
     * @test
     */
    public function it_tests()
    {

        $capabilities = [ 'edit_posts', 'create_posts' ];

        $mockUser = $this->createMock('WP_User');
        $mockUser->expects($this->exactly(2))->method('has_cap')
                 ->willReturnOnConsecutiveCalls($capabilities)->willReturn(true);

        $capabilities = new HasCap($capabilities, $mockUser);

        $result = $capabilities->test();
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_fails()
    {

        $capabilities = [ 'edit_posts', 'create_posts' ];

        $mockUser = $this->createMock('WP_User');
        $mockUser->expects($this->exactly(1))->method('has_cap')
                 ->willReturnOnConsecutiveCalls($capabilities)->willReturnOnConsecutiveCalls(false, true);

        $capabilities = new HasCap($capabilities, $mockUser);

        $result = $capabilities->test();
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_fails_consecutive_calls()
    {

        $capabilities = [ 'edit_posts', 'create_posts' ];

        $mockUser = $this->createMock('WP_User');
        $mockUser->expects($this->exactly(2))->method('has_cap')
                 ->willReturnOnConsecutiveCalls($capabilities)->willReturnOnConsecutiveCalls(true, false);

        $capabilities = new HasCap($capabilities, $mockUser);

        $result = $capabilities->test();
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function the_middleware_tests()
    {
        $container = new Container();
        $this->router($container);
        $capabilities  = [ 'edit_posts', 'create_posts' ];
        $conditionMock = $this->createPartialMock(HasCap::class, [ 'test' ]);
        $conditionMock->expects($this->once())->method('test')->willReturn(true);

        container()->bind(HasCap::class, function () use ($conditionMock) {
            return $conditionMock;
        });

        $this->container()->makeWith(UserHasCap::class, [ 'capabilities' => $capabilities ]);
        $middleware = new UserHasCap($capabilities);

        $response = $middleware->handle(
            $container->make(Request::class),
            $container->make(Response::class),
            function ($request, $response) {
                return $response;
            }
        );

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     */
    public function the_middleware_fails()
    {
        $container = new Container();
        $this->router($container);
        $capabilities  = [ 'edit_posts', 'create_posts' ];
        $conditionMock = $this->createPartialMock(HasCap::class, [ 'test' ]);
        $conditionMock->expects($this->once())->method('test')->willReturn(false);

        container()->bind(HasCap::class, function () use ($conditionMock) {
            return $conditionMock;
        });

        $this->container()->makeWith(UserHasCap::class, [ 'capabilities' => $capabilities ]);
        $middleware = new UserHasCap($capabilities);

        $response = $middleware->handle(
            $container->make(Request::class),
            $container->make(Response::class),
            function ($request, $response) {
                return $response;
            }
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function the_middleware_redirects()
    {
        $container = new Container();
        $this->router($container);
        $capabilities  = [ 'edit_posts', 'create_posts' ];
        $conditionMock = $this->createPartialMock(HasCap::class, [ 'test' ]);
        $conditionMock->expects($this->once())->method('test')->willReturn(false);

        container()->bind(HasCap::class, function () use ($conditionMock) {
            return $conditionMock;
        });

        $middleware = $this->container()->makeWith(UserHasCap::class, [
            'capabilities' => $capabilities,
            'redirect_to'  => '/login'
        ]);

        $response = $middleware->handle(
            $container->make(Request::class),
            $container->make(Response::class),
            function ($request, $response) {
                return $response;
            }
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function condition_can_be_registered_via_string()
    {
        $container = new Container();
        $this->router($container);
        $mockUser          = $this->createMock('WP_User');
        $hasCapFactoryMock = $this->createPartialMock(HasCapFactory::class, [ 'getCurrentUser' ]);
        $hasCapFactoryMock->expects($this->once())->method('getCurrentUser')->willReturn($mockUser);
        $hasCapFactoryMock->container = $container;
        $container->bind(HasCapFactory::class, function () use ($hasCapFactoryMock) {
            return $hasCapFactoryMock;
        });
        $mock            = $this->createPartialMock(ConditionFactory::class, [ 'getRegisteredConditions' ]);
        $mock->container = $container;
        $mock->expects($this->once())->method('getRegisteredConditions')->willReturn([ 'can' => HasCap::class ]);
        $condition = $mock->make('can:edit_posts,create_posts');
        $this->assertInstanceOf(HasCap::class, $condition);
        $this->assertTrue(in_array('edit_posts', $condition->capabilities));
        $this->assertTrue(in_array('create_posts', $condition->capabilities));
    }

    /**
     * @test
     */
    public function middleware_can_be_registered_via_string()
    {
        $container = new Container();
        $this->router($container);
        $mockUser          = $this->createMock('WP_User');
        $hasCapFactoryMock = $this->createPartialMock(UserHasCapFactory::class, [ 'getCurrentUser' ]);
        $hasCapFactoryMock->expects($this->once())->method('getCurrentUser')->willReturn($mockUser);
        $hasCapFactoryMock->container = $container;
        $container->bind(UserHasCapFactory::class, function () use ($hasCapFactoryMock) {
            return $hasCapFactoryMock;
        });
        $mock            = $this->createPartialMock(MiddlewareFactory::class, [ 'getRegisteredMiddleware' ]);
        $mock->container = $container;
        $mock->expects($this->once())->method('getRegisteredMiddleware')->willReturn([ 'can' => UserHasCap::class ]);
        $middleware = $mock->make('can:edit_posts,create_posts');
        $this->assertInstanceOf(UserHasCap::class, $middleware);
        $this->assertTrue(in_array('edit_posts', $middleware->capabilities));
        $this->assertTrue(in_array('create_posts', $middleware->capabilities));
    }
}
