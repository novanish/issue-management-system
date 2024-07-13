<?php

namespace Core\Interfaces;

interface MiddlewareInterface
{
    function process(callable $next): void;
}
