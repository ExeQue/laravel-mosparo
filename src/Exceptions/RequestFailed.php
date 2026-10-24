<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Exceptions;

use Mosparo\ApiClient\Exception as ClientException;
use RuntimeException;
use Throwable;

/**
 * mosparo answered, but with an error or with something that is not a valid answer.
 */
class RequestFailed extends RuntimeException implements MosparoException
{
    /**
     * @param array<string, mixed>|null $debugInformation Details mosparo adds in its debug mode.
     */
    public function __construct(
        string $message,
        ?Throwable $previous = null,
        public readonly ?array $debugInformation = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function refused(string $project, ClientException $previous): self
    {
        return new self(
            "mosparo refused the request for the project [{$project}]: {$previous->getMessage()}",
            $previous,
            $previous->getDebugInformation(),
        );
    }

    public static function unexpected(string $project, Throwable $previous): self
    {
        return new self("mosparo gave an unexpected answer for the project [{$project}].", $previous);
    }
}
