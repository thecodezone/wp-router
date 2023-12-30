<?php

namespace CodeZone\Router\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Render implements Middleware
{

    public function handle(Request $request, Response $response, $next)
    {
        if ($response->get_status() === 200) {
            if (is_array($response->get_data())) {
                echo wp_json_encode($response->get_data());
                exit;
            }
            if (apply_filters('dt_blank_access', false)) {
                add_action('dt_blank_body', function () use ($response) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo $response->get_data();
                });
            } else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $response->get_data();
            }
        }

        return $next($request, $response);
    }
}
