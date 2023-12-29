<?php

namespace CZ\Router\Conditions;

class CallbackCondition implements Condition
{

    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function test(): bool
    {
        $callback = $this->callback;

        return $callback();
    }
}
