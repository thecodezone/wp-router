# CodeZone Router

This package contains a simple routing system implementation that works with Roots-based WordPress plugins or sites, or
any WordPress projects that utilize container.

The router uses the FastRoute dispatching library and integrates with the `Illuminate\Container\Container` class from
the Laravel framework for managing dependencies.

## Links

- [Sage](https://roots.io/): The modern WordPress starter theme with a development workflow.
- [FastRoute](https://github.com/nikic/FastRoute): A fast request router for PHP that is highly flexible and reverse
  routing compatible.
- [Illuminate Container](https://github.com/illuminate/container): The Illuminate Container package is used to manage
  class dependencies and perform dependency injection, part of the Laravel framework.

## Usage

Import the Router class at the top of your PHP file:

```php
use CodeZone\Router\Router;
```

### Registering the Router

You first have to register the router with a container. This can be done inside of a service provider or a plugin's main
file.

```php
$router = Router::register($configArray);
```

In this example, `$configArray` is an array that must contain a `'container'` key with an instance
of `Illuminate\Container\Container` as a value:

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

Route parameters provide dynamic segments in routing paths and allow paths to contain variable parts. This package uses
FastRoute syntax for defining route parameters.

Here's an example of a route with a parameter:

```php
    $r->get('/user/{id}', 'Plugin\Controllers\UserController@show');
```

In this case, `{id}` is a route parameter. When matching routes, FastRoute uses these parameters to capture parts of the
path, which allows for flexible routing strategies.

You can read more about route parameters in the [FastRoute documentation](https://github.com/nikic/FastRoute).

### Conditionals

In this routing system, you can define conditional routes using `$r->condition(conditionClass, callback)`.
The `conditionClass` must be a class that implements a `test()` method returning a boolean. The callback is only
executed if the `test()` method returns `true`.

Here is an example, where `IsFrontendPath` is a class that defines a condition for checking if the current request is
not directed at an admin page:

```php
namespace CodeZone\Router\Conditions;

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

You may also use the `codezone/router/conditions` action to add named conditions:

```php
add_action('codezone/router/conditions', function($conditions) {
    $conditions['isFrontend'] = IsFrontendPath::class;
});
```

This allows for more reader-friendly route condition definitions:

```php
$r->condition('isFrontend', function($r){
    $r->get('/my-route', 'MyController');
});
```

In this scenario, a GET request to '/my-route' is handled by the `'MyController'` handler only if the `IsFrontendPath`
condition is met, that is, only if the request is not an admin request.

### Middleware

Middleware in this routing system provides a set of "layers" through which a request must pass before it reaches your
application handlers, and through which the response must pass on the way back.

Middleware can be used to modify the HTTP request or response, for instance, or to run code before or after the request
handling.

An example of middleware usage is a `LoggedIn` class, which checks if a user is logged in:

```php
namespace CodeZone\Router\Middleware;

use CodeZone\Router\Illuminate\Http\Request;
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

In this example, if the user is not logged in, the middleware sets the response status to 302 and redirects the user to
the login page. Otherwise, it continues the middleware stack to the next middleware.

#### Global Middleware

Global middleware is applied to all routes and is registered by adding Middleware to a `Stack`, which extends Laravel
collections.

Creating a new stack is done by passing an array of middleware class names to the `Stack` constructor. The following
stack will process the entire request / response lifecycle of your theme or plugin.

```php
use CodeZone\Router\Middleware\DispatchController;
use CodeZone\Router\Middleware\HandleErrors;
use CodeZone\Router\Middleware\HandleRedirects;
use CodeZone\Router\Middleware\Render;
use CodeZone\Router\Middleware\Route;
use CodeZone\Router\Middleware\SetHeaders;
use CodeZone\Router\Middleware\Stack;

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

Once you have a Stack instance, you can push middleware classes onto it, to create a stack (or pipeline) of middleware
that your application will run through. The request will move through the stack in the order that middleware is added.
The added middleware wrap around your application handling, allowing them to interact with the request before and after
the application does.

#### Route Middleware

In addition to global middleware that runs for every route, you can define middleware that runs only for specific
routes.

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

These methods allow you to specify middleware that should only be executed for certain routes. This gives you more
fine-grained control over when different middleware should be used in your plugin.

#### Named Middleware

You may also use the `codezone/router/middleware` action to add named middleware:

```php
add_action('codezone/router/middleware', function($middleware) {
    $middleware['auth'] = AuthMiddleware::class;
});
```

This allows for more reader-friendly route middleware definitions:

```php
$r->get('/my-route', 'MyController', ['middleware' => 'auth']);
```

or

```php
$r->middleware('auth', function($r){
    $r->get('/my-route', 'MyController');
});
```

##### HasCap Middleware

This package includes a `UserHasCap` middleware class that checks if the current user has a specific capability. You can
use this middleware to restrict access to certain routes.

Assuming you register the named middleware as `['can' => UserHasCap::class]`, you can use it like this:

```php
$r->get('/my-route', 'MyController', ['middleware' => 'hasCap:manage_options,edit_posts']);
```

## WordPress Hooks

This package uses the `apply_filters` function to give you control over certain functionalities. Below are the hooks you
can use along with their descriptions.

### 'codezone/router/response' filter

This filter modifies the HTTP response from the router:

```php
add_filter('codezone/router/response', function($response) {
    return $response;
});
```

### 'codezone/router/error-codes' filter

This filter is used to modify the error codes.

```php
add_filter('codezone/router/error-codes', function($error_codes) {
    unset($error_codes[404]);
    return $error_codes;
});
```

### 'codezone/router/routable_params' filter

This filter allows you to modify the routable parameters in the router. These parameters are considered part of the
route's path instead of only being passed to the `Request` object.

```php
    add_filter('codezone/router/routable_params', function($params) {
        $params['action', 'page', 'tab'];
    });

    // Would match

    $r->get('/contact?action=submit', 'Plugin\Controllers\ContactController@submit');
```

### 'codezone/router/routes' filter

This filter allows you to modify the routes in the router:

```php
add_filter('codezone/router/routes', function($routes) {
    $routes->get('/my-route', 'MyController');
return $routes;
});
```

### 'codezone/router/matched_routes' filter

This filter allows you to modify the matched routes in the router:

```php
add_filter('codezone/router/matched_routes', function($matchedRoutes) {
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

### 'codezone/router/render' action

An action to render the response. This action is called after the router has matched a route and before the response is
sent to the browser.

```php
add_action('codezone/router/render', function($response) {
    echo $response->getContent();
});
```

### 'codezone/router/render/json' action

An action to render the response as JSON. This action is called after the router has matched a route and before the
response is sent to the browser.

```php
add_action('codezone/router/render/json', function($response) {
    echo json_encode($response->getContent());
});
```

### 'codezone/router/middleware' action

An action for adding named middleware. Allows for more reader-friendly route middleware definitions.

```php
add_action('codezone/router/middleware', function($middleware) {
    $middleware['auth'] = AuthMiddleware::class;
});
```

### 'codezone/router/conditions' action

An action for adding named conditions. Allows for more reader-friendly route condition definitions.

```php
add_action('codezone/router/conditions', function($conditions) {
    $conditions['isFrontend'] = IsFrontendPath::class;
});
```

### 'codezone/router/conditions/factory' filter

A filter for manually handling the instantiation of named conditions. Use this filter if you need to pass extra
to parse the condition signature and pass extra parameters to the condition constructor.

```php
add_filter('codezone/router/conditions/factory', function(Condition|null $instanceOrNull, array $attributes = []) {
    //Check if this is our named condition
    if ($name !== 'can') {
        return $instanceOrNull;
    }
    
    $className = $attributes['className'] ?? null;
    $name = $attributes['name'] ?? null;
    $signature = $attributes['signature'] ?? null;
    
    //The signature is the part of the route name after the ":". We need to break it into an array.
    $params = explode(',', $signature);
    
    return container->makeWith(HasCap::class, ['params' => $params]);
});
```

### 'codezone/router/conditions/factories' filter

As an alternative to using the filter above, you may also implement the `Factory` interface to register a
condition factory.

```php
add_filter('codezone/router/conditions/factory', function(Condition|null $instanceOrNull, array $attributes = []) {
    //Check if this is our named condition
    if ($name !== 'can') {
        return $instanceOrNull;
    }
    
    $className = $attributes['className'] ?? null;
    $name = $attributes['name'] ?? null;
    $signature = $attributes['signature'] ?? null;
    
    //The signature is the part of the route name after the ":". We need to break it into an array.
    $params = explode(',', $signature);
    
    return container->makeWith(HasCap::class, ['params' => $params]);
});
```

### 'codezone/router/middleware/factory' filter

A filter for manually handling the instantiation of named middleware. Use this filter if you need to pass extra
to parse the middleware signature and pass extra parameters to the middleware constructor.

```php
add_filter('codezone/router/middleware/factory', function(Middleware|null $instanceOrNull, array $attributes = []) {
    //Check if this is our named middleware
    if ($name !== 'can') {
        return $instanceOrNull;
    }
    
    $className = $attributes['className'] ?? null;
    $name = $attributes['name'] ?? null;
    $signature = $attributes['signature'] ?? null;
    
    //The signature is the part of the route name after the ":". We need to break it into an array.
    $params = explode(',', $signature);
    
    return container->makeWith(UserHasCap::class, ['params' => $params]);
});
```

### 'codezone/router/middleware/factories' filter

As an alternative to using the filter above, you may also implement the `Factory` interface to register a
middleware factory.

```php
add_filter('codezone/router/conditions/factories', function($factories) {
    $factories[UserHasCap::class] = UserHasCapFactory::class;
    return $factories;
});
```


