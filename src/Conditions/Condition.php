<?php

namespace CodeZone\Router\Conditions;

interface Condition
{
    public function test(): bool;
}
