<?php

namespace App\Twig;

class DictExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter("dict", [$this, 'dict']),
            new \Twig\TwigFilter("dictMulti", [$this, 'dictMulti']),
        ];
    }

    public function dict($key, $map): mixed
    {
        return $map[$key] ?? "";
    }

    public function dictMulti($key, $map): mixed
    {
        if(!empty($key)){
            if(is_string($key)){
                if (!str_contains($key, "[") && !str_contains($key, "{")) {
                    $key = explode(",", $key);
                }else{
                    $key = json_decode($key, true);
                }
            }
        }else{
            return "";
        }
        return implode(",", array_map(function ($v) use ($map) {
            return $map[$v] ?? "";
        }, $key));
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}