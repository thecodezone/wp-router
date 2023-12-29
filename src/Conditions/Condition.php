<?php

namespace CZ\Router\Conditions;

interface Condition
{
    public function test(): bool;
}
