<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Exceptions;

use InvalidArgumentException;

class UnknownTokenSet extends InvalidArgumentException implements MosparoException
{
    public static function named(string $name): self
    {
        return new self("The mosparo token set [{$name}] is not configured as two token names.");
    }
}
