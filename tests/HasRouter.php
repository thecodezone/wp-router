<?php

namespace Tests;

use CodeZone\Router\Router;

trait HasRouter
{
    private $_router;

    public function setUp(): void
    {
        $this->_router;

        parent::setUp();
    }

    protected function router($container = null): Router
    {
        if ($this->_router) {
            return $this->_router;
        }

        return $this->_router = Router::register([
            'container' => $container ?? $this->container(),
        ]);
    }
}
