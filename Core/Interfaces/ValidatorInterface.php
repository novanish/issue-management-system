<?php

namespace Core\Interfaces;

interface ValidatorInterface
{
    function getError(): string | null;
    function getErrors(): array | null;
    function getValue(): mixed;
}
