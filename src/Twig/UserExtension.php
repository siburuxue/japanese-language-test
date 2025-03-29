<?php

namespace App\Twig;

use App\Service\AdminUserService;

class UserExtension extends \Twig\Extension\AbstractExtension
{
    public function __construct(
        private AdminUserService $adminUserService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("getUsername",[$this,'getUsername'])
        ];
    }

    public function getUsername(int $uid)
    {
        $info = $this->adminUserService->getUserInfo($uid);
        return $info?->getUsername();
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}