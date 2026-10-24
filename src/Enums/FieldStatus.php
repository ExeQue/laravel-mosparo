<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Enums;

/**
 * What mosparo found for one field of a verified submission.
 */
enum FieldStatus: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case NotVerified = 'not-verified';
}
