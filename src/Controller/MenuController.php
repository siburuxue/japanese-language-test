<?php

namespace App\Controller;

use App\Lib\Constant\Session;
use App\Lib\Constant\User;
use App\Service\PermissionService;
use App\Service\RouteService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends CommonController
{
    public function tree(Request $request, PermissionService $permissionService, RequestStack $requestStack): Response
    {
        $user = $requestStack->getSession()->get(Session::USER_SESSION_KEY,[]);
        if(in_array($user['id'], User::ADMINISTRATOR_ID)){
            $route = [];
        }else{
            $route = $user['role'];
        }
        $menu = $permissionService->menu($route);
        return $this->render("admin/menu/tree.html.twig", ['menu' => $menu]);
    }

    public function focus(Request $request, PermissionService $permissionService, RouteService $routeService): JsonResponse
    {
        $focus = $request->request->get('focus', '');
        $routeInfo = $routeService->info($focus);
        $menuRoute = $permissionService->getTreeRouteByRoute($routeInfo['_route']);
        $routePath = $this->generateUrl($menuRoute);
        return $this->json(['focus' => $routePath]);
    }
}