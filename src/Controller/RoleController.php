<?php

namespace App\Controller;

use App\Lib\Constant\Tool;
use App\Service\RoleService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends CommonController
{
    public function index(): Response
    {
        return $this->render("admin/role/index.html.twig");
    }

    public function list(Request $request, PaginatorInterface $paginator, RoleService $roleService): Response
    {
        if (!$this->csrfValid()) {
            return new Response(Tool::CSRF_ERROR);
        }
        $roleName = $request->request->get("roleName","");
        $routeName = $request->request->get("routeName","");
        $page = (int)$request->request->get('page', 1);
        $limit = (int)$request->request->get('limit', 10);
        $db = $roleService->list(['roleName' => $roleName,'routeName' => $routeName]);
        $pagination = $paginator->paginate($db, $page, $limit);
        return $this->render("admin/role/list.html.twig", ['pagination' => $pagination]);
    }

    public function add(): Response
    {
        return $this->render('admin/role/add.html.twig');
    }

    public function insert(Request $request, RoleService $roleService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $roleService->insert($data);
        return $this->insertSuccess();
    }

    public function edit($id, RoleService $roleService): Response
    {
        $roleInfo = $roleService->info($id);
        return $this->render("admin/role/edit.html.twig",[
            'info' => $roleInfo
        ]);
    }

    public function update(Request $request, RoleService $roleService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $roleService->update($data);
        return $this->updateSuccess();
    }

    public function delete($id, RoleService $roleService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $roleService->delete($id);
        return $this->deleteSuccess();
    }
}