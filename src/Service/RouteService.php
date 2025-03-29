<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class RouteService
{
    public function __construct(
        private RouterInterface $route,
        private RequestStack $requestStack,
    )
    {}

    public function info(string $routePath): array
    {
        return $this->route->match($routePath);
    }

    public function current(): array
    {
        $routePath = $this->requestStack->getCurrentRequest();
        $route = $this->info($routePath);
        $route['path'] = $routePath;
        return $route;
    }

    public function currentPrefix(): ?string
    {
        $routePath = $this->requestStack->getCurrentRequest();
        $routeArray = explode("/",$routePath);
        array_shift($routeArray);
        return array_shift($routeArray) . "/";
    }
}