<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Exceptions;

use RuntimeException;

class IncompleteProject extends RuntimeException implements MosparoException
{
    /**
     * @param list<string> $missing
     */
    public static function missing(string $name, array $missing): self
    {
        return new self(sprintf(
            'The mosparo project [%s] is missing its %s. Set them, or switch mosparo off with MOSPARO_ENABLED=false.',
            $name,
            implode(', ', $missing),
        ));
    }
}
