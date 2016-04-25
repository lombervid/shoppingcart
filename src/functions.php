<?php
/**
* Recursively computes the intersection of arrays using keys for comparison.
*
* @param   array $array1 The array with master keys to check.
* @param   array $array2 An array to compare keys against.
* @return  array associative array containing all the entries of array1 which have keys that are present in array2.
**/
function array_intersect_key_recursive(array $array1, array $array2)
{
    $array1 = array_intersect_key($array1, $array2);
    foreach ($array1 as $key => &$value) {
        if (is_array($array2[$key])) {
            if (is_array($value)) {
                $value = array_intersect_key_recursive($value, $array2[$key]);
            } else {
                unset($array1[$key]);
            }
        }
    }

    return $array1;
}