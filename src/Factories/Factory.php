<?php

namespace CodeZone\Router\Factories;

interface Factory
{
    public function make(mixed $value = null, iterable $options = []);
}
