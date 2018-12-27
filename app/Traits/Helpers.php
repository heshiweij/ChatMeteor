<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Traits;

trait Helpers
{
    public function chunkCombineArray($array)
    {
        if (empty($array)) {
            return [];
        }

        $keys   = [];
        $values = [];

        for ($i = 0; $i < count($array); $i++) {
            if ($i % 2 == 0) {
                $keys[] = $array[$i];
            } else {
                $values[] = $array[$i];
            }
        }

        return array_combine($keys, $values);
    }
}