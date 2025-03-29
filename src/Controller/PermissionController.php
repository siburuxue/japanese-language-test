<?php

namespace App\Controller;

use App\Service\PermissionService;
use App\Service\RoleService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
class PermissionController extends CommonController
{
    public function index(): Response
    {
        return $this->render("admin/permission/index.html.twig");
    }

    public function tree($type, $role, PermissionService $permissionService, RoleService $roleService): Response
    {
        $tree = $permissionService->tree();
        $role = explode(',',$role);
        $permissionId = $roleService->getPermissionId($role);
        return $this->render("admin/permission/tree.html.twig",['tree' => $tree, 'type' => $type, 'permissionId' => $permissionId]);
    }
}