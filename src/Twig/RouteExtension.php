<?php

namespace App\Twig;

use App\Lib\Constant\Route;
use ReflectionClass;

class RouteExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("constant_route",[$this,'constantRoute'])
        ];
    }

    public function constantRoute(string $key)
    {
        $reflection = new ReflectionClass(Route::class);
        if ($reflection->hasConstant($key)) {
            return $reflection->getConstant($key);
        }else{
            return null;
        }
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}