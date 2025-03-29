<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommonExtension extends \Twig\Extension\AbstractExtension
{
    public function __construct(
        protected UrlGeneratorInterface $router
    ) {}

    protected function createUrl($route, $data): string
    {
        return $this->router->generate($route, $data);

    }

    protected function createButton($route, $data, $button): array|string
    {
        $url = $this->createUrl($route, $data);
        return str_replace("{url}", $url, $button);
    }
}