<?php

namespace CodeZone\Router;

use Illuminate\Container\Container;

/**
 * Get the container instance.
 *
 * @return Container The container instance.
 * @throws Exception if the router instance is not available.
 */
function container(): Container
{
    return Router::instance()->container;
}

/**
 * Determine if a string is a valid JSON.
 *
 * @param string $string The string to check.
 *
 * @return bool True if the string is a valid JSON, otherwise false.
 */
function is_json($string)
{
    if (! is_string($string)) {
        return false;
    }
    json_decode($string);

    return json_last_error() === JSON_ERROR_NONE;
}
