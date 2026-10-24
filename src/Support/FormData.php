<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Support;

/**
 * Shapes submitted fields the way the box saw them, so their hashes match:
 *
 * - strings are trimmed of whitespace, as the box trims them before its check;
 * - `address[street]` and `rows[0][name]` are keys of their own, as in the form;
 * - a list of values (`tags[]`) stays a list;
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

            if (is_array($value) && (! array_is_list($value) || self::hasArrays($value))) {
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
        $scalars = array_filter($values, fn (mixed $value): bool => ! is_object($value));

        return array_values(array_map(self::trim(...), $scalars));
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private static function hasArrays(array $values): bool
    {
        return array_filter($values, is_array(...)) !== [];
    }

    /**
     * Trims whitespace the way the browser's String.prototype.trim() does.
     */
    private static function trim(mixed $value): mixed
    {
        return is_string($value) ? preg_replace('/^[\s\x{FEFF}]+|[\s\x{FEFF}]+$/u', '', $value) : $value;
    }
}
