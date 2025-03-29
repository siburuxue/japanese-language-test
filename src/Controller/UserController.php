<?php

namespace App\Controller;

use App\Lib\Constant\Message;
use App\Lib\Constant\Tool;
use App\Service\AdminUserService;
use App\Service\RoleService;
use App\Service\UserGroupService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends CommonController
{
    public function index(): Response
    {
        return $this->render("admin/user/index.html.twig");
    }

    public function list(Request $request, PaginatorInterface $paginator, AdminUserService $userService): Response
    {
        if (!$this->csrfValid()) {
            return new Response(Tool::CSRF_ERROR);
        }
        $username = $request->request->get("username","");
        $groupName = $request->request->get("groupName","");
        $nickname = $request->request->get("nickname","");
        $page = (int)$request->request->get('page', 1);
        $limit = (int)$request->request->get('limit', 10);
        $db = $userService->list(['username' => $username,'groupName' => $groupName, 'nickname' => $nickname]);
        $pagination = $paginator->paginate($db, $page, $limit);
        return $this->render("admin/user/list.html.twig", ['pagination' => $pagination]);
    }

    public function add(UserGroupService $userGroupService,RoleService $roleService): Response
    {
        $groupList = $userGroupService->list(type:Tool::TYPE_ARRAY);
        $roleList = $roleService->list(type:Tool::TYPE_ARRAY);
        return $this->render('admin/user/add.html.twig', ['groupList' => $groupList,'roleList' => $roleList]);
    }

    public function insert(Request $request, AdminUserService $adminUserService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $exist = $adminUserService->isExist($data['username']);
        if($exist){
            return $this->error(Message::USERNAME_EXIST_MSG);
        }
        $adminUserService->insert($data);
        return $this->insertSuccess();
    }

    public function edit($id, AdminUserService $adminUserService, UserGroupService $userGroupService, RoleService $roleService): Response
    {
        $groupList = $userGroupService->list(type:Tool::TYPE_ARRAY);
        $roleList = $roleService->list(type:Tool::TYPE_ARRAY);
        $info = $adminUserService->detail($id);
        return $this->render("admin/user/edit.html.twig",[
            'groupList' => $groupList,
            'roleList' => $roleList,
            'info' => $info
        ]);
    }

    public function update(Request $request, AdminUserService $adminUserService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $data = $request->request->all();
        $userInfo = $adminUserService->getUserInfo($data['id']);
        if(!empty($userInfo) && !$userInfo->getUsername() == $data['username']){
            $exist = $adminUserService->isExist($data['username']);
            if($exist){
                return $this->error(Message::USERNAME_EXIST_MSG);
            }
        }
        $adminUserService->update($data);
        return $this->updateSuccess();
    }

    public function delete($id, AdminUserService $adminUserService): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $adminUserService->delete($id);
        return $this->deleteSuccess();
    }
}