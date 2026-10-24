<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Exceptions;

use InvalidArgumentException;

class UnknownProject extends InvalidArgumentException implements MosparoException
{
    public static function named(string $name): self
    {
        return new self("The mosparo project [{$name}] is not configured.");
    }
}
