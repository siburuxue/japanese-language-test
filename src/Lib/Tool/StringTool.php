<?php

namespace App\Lib\Tool;

class StringTool
{
    static public function random(int $n): string
    {
        $arr1 = range('a', 'z');
        $arr2 = range('A', 'Z');
        $arr3 = range('0', '9');
        $array = array(...$arr1, ...$arr2, ...$arr3);
        $count = count($array);
        $rs = [];
        for ($i = 0; $i < $n; $i++) {
            $tmp = mt_rand(0, $count - 1);
            $rs[] = $array[$tmp];
        }
        return implode('', $rs);
    }

    public static function getArrayFromString(string $text): array
    {
        if (!str_contains($text, "[") && !str_contains($text, "{")) {
            return explode(",", $text);
        }else{
            return json_decode($text, true);
        }
    }
}