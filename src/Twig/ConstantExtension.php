<?php

namespace App\Twig;

use ReflectionClass;
use ReflectionClassConstant;

class ConstantExtension extends CommonExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("get_struct_constant", [$this, 'getStructConstant']),
        ];
    }

    private function getConstantValue($key)
    {
        if (str_contains($key, '::')) {
            $arr = explode('::', $key);
            if (class_exists($arr[0])) {
                $reflection = new ReflectionClass($arr[0]);
                if ($reflection->hasConstant($arr[1])) {
                    return $reflection->getConstant($arr[1]);
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            if (defined($key)) {
                return constant($key);
            } else {
                return $key;
            }
        }
    }

    public function getStructConstant($constantString)
    {
        $constantArray = explode('.', $constantString);
        $tmp = $this->getConstantValue(array_shift($constantArray));
        foreach ($constantArray as $item) {
            $key = $this->getConstantValue($item);
            if (isset($tmp) && isset($tmp[$key])) {
                $tmp = $tmp[$key];
            } else {
                return null;
            }
        }
        return $tmp;
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}