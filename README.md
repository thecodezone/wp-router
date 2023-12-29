# CodeZone Router

This package contains a simple routing system implementation that works with Roots-based WordPress plugins or sites, or any WordPress projects that utilize container.

The router uses the FastRoute dispatching library and integrates with the `Illuminate\Container\Container` class from the Laravel framework for managing dependencies.

## Links

- [Sage](https://roots.io/): The modern WordPress starter theme with a development workflow.
- [FastRoute](https://github.com/nikic/FastRoute): A fast request router for PHP that is highly flexible and reverse routing compatible.
- [Illuminate Container](https://github.com/illuminate/container): The Illuminate Container package is used to manage class dependencies and perform dependency injection, part of the Laravel framework.

## Usage

Import the Router class at the top of your PHP file:

```php
use CZ\Router\Router;
```

### Registering the Router

You first have to register the router with a container. This  can be done inside of a service provider or a plugin's main file.

```php
$router = Router::register($configArray);
```

In this example, `$configArray` is an array that must contain a `'container'` key with an instance of `Illuminate\Container\Container` as a value:

```php
$configArray = [
    'container' => $containerInstance // Illuminate\Container\Container
];
```

### Defining Routes

Once the router is registered, you can define routes via a callback function:

```php
$dispatcher = $router->routes(function (Routes $r) {
    $r->get('/my-route', [Plugin\Controllers\MyController::class, 'index']);
    //or
    $r->get('/my-route', 'Plugin\Controllers\MyController@index');
    //or
    $r->get('/my-route', function () {
        return "<h1>Hello World!</h1>";
    });
});
```


### Route Parameters

Route parameters provide dynamic segments in routing paths and allow paths to contain variable parts. This package uses FastRoute syntax for defining route parameters.

Here's an example of a route with a parameter:

```php
    $r->get('/user/{id}', 'Plugin\Controllers\UserController@show');
```

In this case, `{id}` is a route parameter. When matching routes, FastRoute uses these parameters to capture parts of the path, which allows for flexible routing strategies.

You can read more about route parameters in the [FastRoute documentation](https://github.com/nikic/FastRoute).

### Conditionals

In this routing system, you can define conditional routes using `$r->condition(conditionClass, callback)`. The `conditionClass` must be a class that implements a `test()` method returning a boolean. The callback is only executed if the `test()` method returns `true`.

Here is an example, where `IsFrontendPath` is a class that defines a condition for checking if the current request is not directed at an admin page:

```php
namespace CZ\Router\Conditions;

class IsFrontendPath implements Condition {
    public function test(): bool {
        return ! is_admin();
    }
}
```

You would use it in your routes definition like this to exclude these routes from being registered on admin pages:

```php
$r->condition(IsFrontendPath::class, function($r){
    $r->get('/my-route', 'MyController');
});
```

In this scenario, a GET request to '/my-route' is handled by the `'MyController'` handler only if the `IsFrontendPath` condition is met, that is, only if the request is not an admin request.

### Middleware

Middleware in this routing system provides a set of "layers" through which a request must pass before it reaches your application handlers, and through which the response must pass on the way back.

Middleware can be used to modify the HTTP request or response, for instance, or to run code before or after the request handling.

An example of middleware usage is a `LoggedIn` class, which checks if a user is logged in:

```php
namespace CZ\Router\Middleware;

use CZ\Router\Illuminate\Http\Request;
use WP_HTTP_Response;

class LoggedIn implements Middleware {

    public function handle( Request $request, WP_HTTP_Response $response, $next ) {
    
        if ( ! is_user_logged_in() ) {
            $response->set_status( 302 );
            $response->set_data( wp_login_url( $request->getUri() ) );
        }

        return $next( $request, $response );
    }
}
```

In this example, if the user is not logged in, the middleware sets the response status to 302 and redirects the user to the login page. Otherwise, it continues the middleware stack to the next middleware.

#### Global Middleware

Global middleware is applied to all routes and is registered by adding Middleware to a `Stack`, which extends Laravel collections.

Creating a new stack is done by passing an array of middleware class names to the `Stack` constructor. The following stack will process the entire request / response lifecycle of your theme or plugin.

```php
use CZ\Router\Middleware\DispatchController;
use CZ\Router\Middleware\HandleErrors;
use CZ\Router\Middleware\HandleRedirects;
use CZ\Router\Middleware\Render;
use CZ\Router\Middleware\Route;
use CZ\Router\Middleware\SetHeaders;
use CZ\Router\Middleware\Stack;

$middleware = [
    Route::class,
    DispatchController::class,
    SetHeaders::class,
    HandleRedirects::class,
    HandleErrors::class,
    Render::class,
];

$stack = container()->makeWith(Stack::class, $middleware);

$stack->run();
```

Once you have a Stack instance, you can push middleware classes onto it, to create a stack (or pipeline) of middleware that your application will run through. The request will move through the stack in the order that middleware is added. The added middleware wrap around your application handling, allowing them to interact with the request before and after the application does.

#### Route Middleware

In addition to global middleware that runs for every route, you can define middleware that runs only for specific routes.

You can apply route middleware by chaining the `->middleware()` method after defining a route:

```php
$r->get('/my-route', 'MyController')->middleware(Middleware::class);
```

In this example, `Middleware::class` will only be applied to the '/my-route' route.

Note that like conditionals, you can pass a callback to the `middleware()` method:

```php
$r->get('/my-route', 'MyController')->middleware(Middleware::class, function () {
//...
});
```

The second way to apply middleware to a route is by including it in the third value of the handler array:

```php
$r->get('/my-route', ['MyController', 'myMethod', ['middleware' => Middleware::class]]);
//or
$r->get('/my-route', 'MyController')->middleware([MiddlewareOne::class, MiddlewareTwo::class]);
```

In this example, `Middleware::class` is applied to just the `/my-route` route.

These methods allow you to specify middleware that should only be executed for certain routes. This gives you more fine-grained control over when different middleware should be used in your plugin.

## WordPress Hooks

This package uses the `apply_filters` function to give you control over certain functionalities. Below are the hooks you can use along with their descriptions.


### 'cz/router/response' filter

This filter modifies the HTTP response from the router:

```php
add_filter('cz/router/response', function($response) {
    return $response;
});
```

### 'cz/router/error-codes' filter

This filter is used to modify the error codes.

```php
add_filter('cz/router/error-codes', function($error_codes) {
    unset($error_codes[404]);
    return $error_codes;
});
```

### 'cz/router/routable_params' filter

This filter allows you to modify the routable parameters in the router. These parameters are considered part of the route's path instead of only being passed to the `Request` object.

```php
    add_filter('cz/router/routable_params', function($params) {
        $params['action', 'page', 'tab'];
    });

    // Would match

    $r->get('/contact?action=submit', 'Plugin\Controllers\ContactController@submit');
```

### 'cz/router/routes' filter

This filter allows you to modify the routes in the router:

```php
add_filter('cz/router/routes', function($routes) {
    $routes->get('/my-route', 'MyController');
return $routes;
});
```

### 'cz/router/matched_routes' filter

This filter allows you to modify the matched routes in the router:

```php
add_filter('cz/router/matched_routes', function($matchedRoutes) {
    $matchedRoutes[0] =  true;
    $matchedRoutes[1] =  [
        'handler' => [
           'Plugin\Controllers\MyController'
           'myMethod'
        ],
        'params' => [
            'id' => '3',
        ]
    ]
    return $matchedRoute;
});
```