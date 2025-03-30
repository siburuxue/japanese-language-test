<?php

namespace App\Service;

use App\Lib\Constant\Dict;
use App\Lib\Constant\Tool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use App\Entity\Log;
use Symfony\Component\HttpFoundation\RequestStack;

class LogService extends CommonService
{
    private ObjectRepository $logRepository;

    private array $item = [];

    private array $param = [];

    public function __construct(
        private ManagerRegistry $doctrine,
        private RequestStack $requestStack,
        private UserGroupService $userGroupService,
        private RoleService $roleService,
        private AdminUserService $adminUserService,
        private PermissionService $permissionService,
        private DictService $dictService,
        private TestPaperService $testPaperService,
    ) {
        parent::__construct($requestStack);
        $this->logRepository = $doctrine->getRepository(Log::class);
    }

    public function __call(string $name, array $arguments)
    {
        $this->param = $arguments[1];
        $user = $this->getSessionUser();
        if (!empty($user)) {
            $this->item['uid'] = $user['id'];
            $this->item['username'] = $user['username'];
            $this->item['createTime'] = time();
            if ($name === "add") {
                $function = str_replace('-', '', ucwords($arguments[0], "-"));
                $function = lcfirst($function);
                if (method_exists($this, $function)) {
                    $this->$function($arguments[1]);
                }
            }
        }
    }

    public function list(array $param)
    {
        return $this->logRepository->list($param);
    }

    private function save(string $content): void
    {
        $this->item['content'] = $content;
        $this->item['param'] = $this->param;
        $this->logRepository->insert($this->item);
    }

    public function login(array $data): void
    {
        $uid = $data['id'];
        $username = $data['username'];
        $content = "登录系统";
        $item = [];
        $item['uid'] = $uid;
        $item['username'] = $username;
        $item['content'] = $content;
        $item['createTime'] = time();
        $this->logRepository->insert($item);
    }

    public function logOut(array $data): void
    {
        $this->save("登出系统");
    }

    public function userInsert(array $data): void
    {
        $groupInfo = $this->userGroupService->info($data['groupId']);
        $role = $this->roleService->list(['roleId' => $data['roleId']], Tool::TYPE_JSON);
        $roleName = array_column($role, 'roleName');
        $content = "添加用户 用户名：「" . $data['username'] . "」昵称：「" . $data['nickname'] . "」密码：「" . $data['password'] . "」";
        $content .= " 用户组：「" . $groupInfo['info']->getGroupName() . "」";
        $content .= " 规则：「" . implode('， ', $roleName) . "」";
        $this->save($content);
    }

    public function userUpdate(array $data): void
    {
        $user = $this->adminUserService->detail($data['id']);
        $default = "修改用户 " . $user['info']->getUsername() . "(" . $user['info']->getId() . ")";
        $content = $default;
        if ($user['info']->getUsername() != $data['username']) {
            $content .= " 用户名由「" . $user['info']->getUsername() . "」修改为「" . $data['username'] . "」";
        }
        if ($user['info']->getNickname() != $data['nickname']) {
            $content .= " 昵称由「" . $user['info']->getNickname() . "」修改为「" . $data['nickname'] . "」";
        }
        if (!empty($data['password']) && !password_verify($data['password'], $user['info']->getPassword())) {
            $content .= " 密码修改为「" . $data['password'] . "」";
        }
        $groupInfo = $this->userGroupService->info($data['groupId']);
        if(empty($user['groupIdList'])){
            $content .= " 用户组由「」修改为「" . $groupInfo['info']->getGroupName() . "」";
        }else{
            if ($user['groupIdList'][0] != $data['groupId']) {
                $originGroupInfo = $this->userGroupService->info($user['groupIdList'][0]);
                $content .= " 用户组由「" . $originGroupInfo['info']->getGroupName() . "」修改为「" . $groupInfo['info']->getGroupName() . "」";
            }
        }
        $diff = array_diff($user['roleIdList'], $data['roleId']);
        if (count($diff) > 0) {
            $role = $this->roleService->list(['roleId' => $data['roleId']], Tool::TYPE_JSON);
            $roleName = array_column($role, 'roleName');
            $originRole = $this->roleService->list(['roleId' => $user['roleIdList']], Tool::TYPE_JSON);
            $originRoleName = array_column($originRole, 'roleName');
            $content .= " 规则由「" . implode("， ", $originRoleName) . "」修改为「" . implode(", ", $roleName) . "」";
        }
        if ($content != $default) {
            $this->save($content);
        }
    }

    public function userDelete(array $data): void
    {
        $user = $this->adminUserService->getUserInfo($data['id']);
        $content = "删除用户「" . $user->getUsername() . "(" . $user->getId() . ")" . "」";
        $this->save($content);
    }

    public function roleInsert(array $data): void
    {
        $default = $this->permissionService->getDefaultRouteId();
        $permissionId = array_diff($data['permissionId'], $default);
        $permission = $this->permissionService->getPermissionRouteName($permissionId);
        $content = "添加规则「" . $data['roleName'] . "」权限列表「" . implode("， ", $permission) . "」";
        $this->save($content);
    }

    public function roleUpdate(array $data): void
    {
        $roleInfo = $this->roleService->info($data['id']);
        $defaultPermission = $this->permissionService->getDefaultRouteId();
        $permissionId = array_diff($data['permissionId'], $defaultPermission);
        $originPermissionId = $this->roleService->getPermissionId([$data['id']]);
        $add = array_diff($permissionId, $originPermissionId);
        $delete = array_diff($originPermissionId, $permissionId);
        $default = "修改规则 " . $roleInfo->getRoleName() . "(" . $roleInfo->getId() . ")";
        $content = $default;
        if ($roleInfo->getRoleName() != $data['roleName']) {
            $content .= " 规则名称由「" . $roleInfo->getRoleName() . "」修改为「" . $data['roleName'] . "」";
        }
        if (!empty($add)) {
            $permission = $this->permissionService->getPermissionRouteName($add);
            $content .= " 新增权限列表「" . implode("， ", $permission) . "」";
        }
        if (!empty($delete)) {
            $permission = $this->permissionService->getPermissionRouteName($delete);
            $content .= " 删除权限列表「" . implode("， ", $permission) . "」";
        }
        if ($content != $default) {
            $this->save($content);
        }
    }

    public function roleDelete(array $data): void
    {
        $roleInfo = $this->roleService->info($data['id']);
        $content = "删除规则「" . $roleInfo->getRoleName() . "(" . $roleInfo->getId() . ")" . "」";
        $this->save($content);
    }

    public function userGroupInsert(array $data): void
    {
        $roleList = $this->roleService->list(['roleId' => $data['roleId']], Tool::TYPE_JSON);
        $roleList = array_column($roleList, 'roleName');
        $content = "新增用户组「" . $data['groupName'] . "」规则列表「" . implode("， ", $roleList) . "」";
        $this->save($content);
    }

    public function userGroupUpdate(array $data): void
    {
        $userGroupInfo = $this->userGroupService->info($data['id']);
        $default = "修改用户组 " . $userGroupInfo['info']->getGroupName() . "(" . $userGroupInfo['info']->getId() . ")";
        $content = $default;
        if ($userGroupInfo['info']->getGroupName() != $data['groupName']) {
            $content .= " 用户组名称由「" . $userGroupInfo['info']->getGroupName() . "」修改为「" . $data['groupName'] . "」";
        }
        $add = array_diff($data['roleId'], $userGroupInfo['userGroupRole']);
        $delete = array_diff($userGroupInfo['userGroupRole'], $data['roleId']);
        if (!empty($add)) {
            $roleList = $this->roleService->list(['roleId' => $add], Tool::TYPE_JSON);
            $roleList = array_column($roleList, 'roleName');
            $content .= " 新增规则列表「" . implode("， ", $roleList) . "」";
        }
        if (!empty($delete)) {
            $roleList = $this->roleService->list(['roleId' => $delete], Tool::TYPE_JSON);
            $roleList = array_column($roleList, 'roleName');
            $content .= " 删除规则列表「" . implode("， ", $roleList) . "」";
        }
        if ($content != $default) {
            $this->save($content);
        }
    }

    public function userGroupDelete(array $data): void
    {
        $userGroupInfo = $this->userGroupService->info($data['id']);
        $content = "删除用户组 「" . $userGroupInfo['info']->getGroupName() . "(" . $userGroupInfo['info']->getId() . ")" . "」";
        $this->save($content);
    }

    public function testPaperInsert(array $data): void
    {
        $map = $this->dictService->getDictMap(Dict::DICT_KEY_DIFFICULTY);
        $difficulty = $data['difficulty'];
        $content = $data['content'];
        $json = json_decode($data['content'], 1);
        $text = "添加题库 「{$json['output']['exam_info']['title']}」难度：「{$map[$difficulty]}」 试卷内容：「{$content}」";
        $this->save($text);
    }

    public function testPaperDelete(array $data): void
    {
        $paper = $this->testPaperService->info($data['id']);
        $content = "删除题库 「" . $paper['title'] . " ({$data['id']})」";
        $this->save($content);
    }

    public function testPaperSetDifficulty(array $data): void
    {
        $paper = $this->testPaperService->info($data['id']);
        $map = $this->dictService->getDictMap(Dict::DICT_KEY_DIFFICULTY);
        $old = $paper['difficulty'];
        $new = $data['difficulty'];
        if($new !== $old){
            $content = "修改题库「{$paper['title']} ({$data['id']})」难度 从「{$map[$old]}」修改为「{$map[$new]}」";
            $this->save($content);
        }
    }

    public function testPaperUploadListeningRecording(array $data): void
    {
        $paper = $this->testPaperService->info($data['id']);
        $url = $data['ossUrl'];
        $content = "修改题库「{$paper['title']} ({$data['id']})」 上传听力音频文件 「{$url}」";
        $this->save($content);
    }

    public function testPaperDeleteListeningRecording(array $data): void
    {
        $paper = $this->testPaperService->info($data['id']);
        $content = "修改题库「{$paper['title']} ({$data['id']})」 删除听力音频文件 ";
        $this->save($content);
    }
}