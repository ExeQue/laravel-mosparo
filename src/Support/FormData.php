<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Support;

use Illuminate\Support\Str;

/**
 * Shapes submitted fields the way the box saw them, so their hashes match:
 *
 * - strings are trimmed, as the box trims them before its check;
 * - `address[street]` is a key of its own, as in the form, not a nested array;
 * - lists (`tags[]`) stay lists;
 * - files and other objects are left out, as the box leaves out file fields.
 */
final class FormData
{
    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    public static function prepare(array $data, string $prefix = ''): array
    {
        $prepared = [];

        foreach ($data as $key => $value) {
            $name = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";

            if (is_object($value)) {
                continue;
            }

            if (is_array($value) && ! array_is_list($value)) {
                $prepared += self::prepare($value, $name);

                continue;
            }

            $prepared[$name] = is_array($value) ? self::list($value) : self::trim($value);
        }

        return $prepared;
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private static function list(array $values): array
    {
        $scalars = array_filter($values, fn (mixed $value): bool => ! is_object($value) && ! is_array($value));

        return array_values(array_map(self::trim(...), $scalars));
    }

    private static function trim(mixed $value): mixed
    {
        return is_string($value) ? Str::trim($value) : $value;
    }
}
