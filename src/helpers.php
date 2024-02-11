<?php

namespace CodeZone\Router;

use CodeZone\Router;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;

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


/**
 * Creates a new Collection instance from the given items.
 *
 * @param array $items The items to be collected (optional, default: empty array)
 *
 * @return Collection The new Collection instance populated with the given items
 */
function collect($items = []): Collection
{
    return new Collection($items);
}


/**
 * Concatenates the given string to the namespace of the Router class.
 *
 * @param string $string The string to be concatenated to the namespace.
 *
 * @return string The result of concatenating the given string to the namespace of the Router class.
 */
function namespace_string(string $string)
{
    return Router::class . '\\' . $string;
}
