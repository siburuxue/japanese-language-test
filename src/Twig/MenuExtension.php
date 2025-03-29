<?php

namespace App\Twig;

use App\Lib\Constant\Menu;
use App\Lib\Constant\Permission;
use App\Lib\Constant\Session;
use App\Lib\Constant\User;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtension extends \Twig\Extension\AbstractExtension
{


    public function __construct(
        private RequestStack $requestStack,
        private RouteExtension $routeExtension,
    ) {}

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("hasPermission", [$this, 'hasPermission']),
            new \Twig\TwigFunction("anyPermissionList", [$this, 'anyPermissionList']),
        ];
    }

    private function has(array $menuRole): bool
    {
        $session = $this->requestStack->getSession();
        $user = $session->get(Session::USER_SESSION_KEY, []);
        if(in_array($user['id'], User::ADMINISTRATOR_ID)){
            return true;
        }
        $rs = array_intersect($menuRole, $user['role']);
        return count($rs) > 0;
    }

    public function anyPermissionList(array $menuRole): bool
    {
        $role = array_map(function($v){
            return $this->routeExtension->constantRoute($v);
        }, $menuRole);
        return $this->has($role);
    }

    public function hasPermission(string $key): bool
    {
        $route = $this->routeExtension->constantRoute($key);
        return $this->has([$route]);
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}
