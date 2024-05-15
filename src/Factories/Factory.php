<?php

namespace CodeZone\Router\Factories;

interface Factory
{
    public function make($value = null, iterable $options = []);
}
