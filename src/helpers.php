<?php

namespace CodeZone\Router;

use Illuminate\Container\Container;

/**
 * @throws \Exception
 */
function container(): Container
{
    return Router::instance()->container;
}

function is_json($string)
{
    if (! is_string($string)) {
        return false;
    }
    json_decode($string);

    return json_last_error() === JSON_ERROR_NONE;
}
