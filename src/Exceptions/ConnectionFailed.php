<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Exceptions;

use RuntimeException;
use Throwable;

/**
 * mosparo could not be reached: the connection failed or timed out.
 */
class ConnectionFailed extends RuntimeException implements MosparoException
{
    public static function to(string $project, Throwable $previous): self
    {
        return new self("Could not reach mosparo for the project [{$project}].", 0, $previous);
    }
}
