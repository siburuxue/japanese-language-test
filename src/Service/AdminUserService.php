<?php

namespace App\Service;

use App\Entity\AdminUser;
use App\Entity\Log;
use App\Entity\UserGroupUser;
use App\Entity\UserRole;
use App\Lib\Constant\Code;
use App\Lib\Constant\Session;
use App\Lib\Constant\Tool;
use App\Lib\Constant\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminUserService extends CommonService
{
    private ObjectRepository $adminUserRepository;
    private ObjectRepository $userRoleRepository;
    private ObjectRepository $userGroupUserRepository;
    private ObjectRepository $logRepository;

    public function __construct(
        private ManagerRegistry $doctrine,
        private UserGroupService $userGroupService,
        private PermissionService $permissionService,
        private RoleService $roleService,
        private RequestStack $requestStack
    ) {
        parent::__construct($requestStack);
        $this->adminUserRepository = $doctrine->getRepository(AdminUser::class);
        $this->userRoleRepository = $doctrine->getRepository(UserRole::class);
        $this->userGroupUserRepository = $doctrine->getRepository(UserGroupUser::class);
        $this->logRepository = $doctrine->getRepository(Log::class);
    }

    public function getUserInfo(int $id)
    {
        return $this->adminUserRepository->find($id);
    }
    
    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function loginCheck(string $username, string $password): bool
    {
        $result = $this->checkUser($username, $password);
        if (!empty($result)) {
            $this->setLastLoginTime($result[0]['id']);
            $user = [
                'id' => $result[0]['id'],
                'username' => $result[0]['username'],
                'nickname' => $result[0]['nickname'],
            ];
            $session = $this->requestStack->getSession();
            if (in_array($result[0]['id'], User::ADMINISTRATOR_ID)) {
                $role = $this->permissionService->getRouteArray();
                $user['role'] = $role;
            } else {
                $role = $this->getPermissionListByUserId($result[0]['id']);
                $user['role'] = $role;
            }
            // 添加默认权限
            $user['role'] = array_merge($user['role'] , $this->permissionService->getDefaultRoute());
            $session->set(Session::USER_SESSION_KEY, $user);
            $this->logRepository->insert([
                'uid' => $user['id'],
                'username' => $user['username'],
                'content' => '登录系统',
                'param' => [],
                'createTime' => time(),
            ]);
        }
        return count($result) > 0;
    }

    public function list(array $array)
    {
        if(isset($array['groupName']) && !empty($array['groupName'])){
            $userGroup = $this->userGroupService->list(['groupName' => $array['groupName']], Tool::TYPE_JSON);
            $userGroupId = array_column($userGroup, 'id');
            if(empty($userGroupId)){
                $array['id'] = [-1];
            }else{
                $userId = $this->userGroupUserRepository->getUserId($userGroupId);
                if(empty($userId)){
                    $array['id'] = [-1];
                }else{
                    $array['id'] = $userId;
                }
            }
        }
        $db = $this->adminUserRepository->list($array);
        return $db;
    }

    public function insert(array $data)
    {
        $item = $this->getCreateInfo();
        $item['username'] = $data['username'];
        $item['nickname'] = $data['nickname'];
        $item['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = $this->adminUserRepository->insert($item);

        $item = $this->getCreateInfo();
        $item['userId'] = $id;
        $item['groupId'] = $data['groupId'];
        $this->userGroupUserRepository->insert($item);

        $existGroupRoleId = $this->userGroupService->getUserGroupRole([$data['groupId']]);
        $add = array_diff($data['roleId'], $existGroupRoleId);
        $this->insertUserRole($id, $add);
    }

    private function insertUserRole($id, $roleIdArray)
    {
        foreach ($roleIdArray as $roleId) {
            $item = $this->getCreateInfo();
            $item['roleId'] = $roleId;
            $item['userId'] = $id;
            $this->userRoleRepository->insert($item);
        }
    }

    public function isExist(string $username)
    {
        return $this->adminUserRepository->isExist($username);
    }

    private function deleteUsreRole($id, $roleIdArray)
    {
        foreach ($roleIdArray as $roleId) {
            $item = [];
            $item['roleId'] = $roleId;
            $item['userId'] = $id;
            $this->userRoleRepository->delete($item);
        }
    }

    public function detail($id)
    {
        $info = $this->adminUserRepository->find($id);
        $groupIdList = $this->userGroupUserRepository->getGroupId([$id]);
        if(empty($groupIdList)){
            $existGroupRoleId = [];
        }else{
            $existGroupRoleId = $this->userGroupService->getUserGroupRole($groupIdList);
        }
        $roleIdList = $this->userRoleRepository->getRoleId([$id]);
        $roleIdList = array_merge($roleIdList, $existGroupRoleId);
        return ['info' => $info, 'groupIdList' => $groupIdList, 'roleIdList' => $roleIdList];
    }

    public function delete($id)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['isDel'] = Code::DELETED;
        $this->adminUserRepository->delete($item);
    }

    public function update(array $data)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $data['id'];
        $item['username'] = $data['username'];
        $item['nickname'] = $data['nickname'];
        if (isset($data['password']) && !empty($data['password'])) {
            $item['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->adminUserRepository->update($item);

        $item = [];
        $item['userId'] = $data['id'];
        $item['groupId'] = $data['groupId'];
        $userGroupUser = $this->userGroupUserRepository->findBy($item);
        if(empty($userGroupUser)){
            $create = $this->getCreateInfo();
            $this->userGroupUserRepository->insert($item + $create);
        }else{
            $this->userGroupUserRepository->update($item);
        }
        $groupRoleId = $this->userGroupService->getUserGroupRole([$data['groupId']]);
        $existUserRoleId = $this->userRoleRepository->getRoleId([$data['id']]);
        $data['roleId'] = array_diff($data['roleId'], $groupRoleId);
        $add = array_diff($data['roleId'], $existUserRoleId);
        $delete = array_diff($existUserRoleId, $data['roleId']);
        $this->insertUserRole($data['id'], $add);
        $this->deleteUsreRole($data['id'], $delete);
    }

    public function getPermissionListByUserId(int $id)
    {
        $userRole = $this->userRoleRepository->getRoleId([$id]);
        $userGroup = $this->userGroupUserRepository->getGroupId([$id]);
        if(!empty($userGroup)){
            $groupRole = $this->userGroupService->getUserGroupRole($userGroup);
        }else{
            $groupRole = [];
        }
        $role = array_merge($userRole, $groupRole);
        if(!empty($role)){
            $permissionId = $this->roleService->getPermissionId($role);
            return $this->permissionService->getPermissionRoute($permissionId);
        }else{
            return [];
        }
    }

    private function checkUser(string $username, string $password): array
    {
        $user = $this->adminUserRepository->getInfoByUsername($username);
        if(count($user) === 0){
            return [];
        }
        $bl = password_verify($password, $user[0]['password']);
        return $bl ? $user : [];
    }

    private function setLastLoginTime(int $id)
    {
        $this->adminUserRepository->lastLogin([
            'id' => $id,
            'lastLogin' => time(),
            'updateTime' => time()
        ]);
    }

    public function getIdsByNickname(string $nickname): array
    {
        $db = $this->adminUserRepository->list(['nickname' => $nickname]);
        $rs = $db->getQuery()->getArrayResult();
        return array_column($rs, 'id');
    }
}