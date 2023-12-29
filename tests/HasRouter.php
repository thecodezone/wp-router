<?php

namespace Tests;

use CZ\Router\Router;
use Illuminate\Container\Container;

trait HasRouter {

	protected function router( $container = null ): Router {
		return Router::register([
			'container' => $container ?? $this->container(),
		]);
	}
}