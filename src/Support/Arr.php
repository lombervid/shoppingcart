<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Support;

class Arr
{
    /**
     * Recursively computes the intersection of arrays using keys for comparison.
     *
     * @param   array $array1 The array with master keys to check.
     * @param   array $array2 An array to compare keys against.
     *
     * @return  array associative array containing all the entries of array1 which have keys that are present in array2.
     **/
    public static function intersectKeyRecursive(array $array1, array $array2): array
    {
        $array1 = array_intersect_key($array1, $array2);

        foreach ($array1 as $key => &$value) {
            if (is_array($array2[$key])) {
                if (is_array($value)) {
                    $value = static::intersectKeyRecursive($value, $array2[$key]);
                } else {
                    unset($array1[$key]);
                }
            }
        }

        return $array1;
    }

    /**
     * Get value from array
     *
     * @param  array        $arr        Array to look for
     * @param  int|string   $key        Position to look for
     * @param  mixed        $default    Default Value
     * @param  string|array $type       Expected type of the value (a gettype() valid value)
     * @param  bool         $empty      Determine whitch function use (isset/empty)
     *
     * @return mixed of the position or $default value
     */
    public static function get(
        array $arr,
        int|string $key,
        mixed $default = null,
        string|array $type = null,
        bool $empty = true,
    ) {
        if (!is_array($arr)) {
            return $default;
        }

        if (!array_key_exists($key, $arr)) {
            return $default;
        }

        if (!empty($type)) {
            if (gettype($type) == 'string') {
                if (gettype($arr[$key]) != $type) {
                    return $default;
                }
            } elseif (gettype($type) == 'array') {
                if (!in_array(gettype($arr[$key]), $type)) {
                    return $default;
                }
            }
        }

        if ($empty) {
            return empty($arr[$key]) ? $default : $arr[$key];
        }

        return $arr[$key];
    }
}
