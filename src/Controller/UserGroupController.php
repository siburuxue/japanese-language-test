<?php

namespace App\Controller;

use App\Lib\Constant\Tool;
use App\Service\RoleService;
use App\Service\UserGroupService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserGroupController extends CommonController
{
    public function index(): Response
    {
        return $this->render("admin/user-group/index.html.twig");
    }

    public function list(Request $request, PaginatorInterface $paginator, UserGroupService $userGroupService): Response
    {
        if (!$this->csrfValid()) {
            return new Response(Tool::CSRF_ERROR);
        }
        $roleName = $request->request->get("roleName","");
        $groupName = $request->request->get("groupName","");
        $page = (int)$request->request->get('page', 1);
        $limit = (int)$request->request->get('limit', 10);
        $db = $userGroupService->list(['roleName' => $roleName,'groupName' => $groupName]);
        $pagination = $paginator->paginate($db, $page, $limit);
        return $this->render("admin/user-group/list.html.twig", ['pagination' => $pagination]);
    }

    public function add(RoleService $roleService): Response
    {
        $roleList = $roleService->list(type:Tool::TYPE_ARRAY);
        return $this->render('admin/user-group/add.html.twig',['roleList' => $roleList]);
    }

    public function insert(Request $request, UserGroupService $userGroupService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $userGroupService->insert($data);
        return $this->insertSuccess();
    }

    public function edit($id, UserGroupService $userGroupService, RoleService $roleService): Response
    {
        $roleList = $roleService->list(type:Tool::TYPE_ARRAY);
        $groupInfo = $userGroupService->info($id);
        return $this->render("admin/user-group/edit.html.twig",[
            'info' => $groupInfo,
            'roleList' => $roleList
        ]);
    }

    public function update(Request $request, UserGroupService $userGroupService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $userGroupService->update($data);
        return $this->updateSuccess();
    }

    public function delete($id, UserGroupService $userGroupService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $userGroupService->delete($id);
        return $this->deleteSuccess();
    }

    public function info($id, UserGroupService $userGroupService, RoleService $roleService)
    {
        $roleList = $roleService->list(type:Tool::TYPE_ARRAY);
        $groupInfo = $userGroupService->info($id);
        return $this->render("admin/user-group/info.html.twig",[
            'info' => $groupInfo,
            'roleList' => $roleList
        ]);
    }

    public function getRoleId($id, UserGroupService $userGroupService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $roleId =$userGroupService->getUserGroupRole([$id]);
        return $this->json(['data' => $roleId]);
    }
}