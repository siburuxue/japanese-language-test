<?php

namespace App\Service;

use App\Lib\Constant\Code;
use App\Lib\Constant\Tool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use App\Entity\UserGroup;
use App\Entity\UserGroupRole;
use Symfony\Component\HttpFoundation\RequestStack;

class UserGroupService extends CommonService
{
	private ObjectRepository $userGroupRepository;
	private ObjectRepository $userGroupRoleRepository;

    public function __construct(
        private ManagerRegistry $doctrine,
        private RoleService $roleService,
        private RequestStack $requestStack
    )
    {
        parent::__construct($requestStack);
		$this->userGroupRepository = $doctrine->getRepository(UserGroup::class);
		$this->userGroupRoleRepository = $doctrine->getRepository(UserGroupRole::class);
    }

    public function list(array $param = [], string $type = '')
    {
        if(isset($param['roleName']) && !empty($param['roleName'])){
            $roleRs = $this->roleService->list($param,Tool::TYPE_JSON);
            $roleId = array_column($roleRs,'id');
            if(!empty($roleId)){
                $groupId = $this->userGroupRoleRepository->getGroupIdArray($roleId);
                if(!empty($groupId)){
                    $param['groupId'] = $groupId;
                }
            }
        }
        $db = $this->userGroupRepository->list($param);
        if($type == Tool::TYPE_ARRAY){
            return $db->getQuery()->getResult();
        }
        if($type == Tool::TYPE_JSON){
            return $db->getQuery()->getArrayResult();
        }
        return $db;
    }

    public function insert(array $data)
    {
        $item = $this->getCreateInfo();
        $item['groupName'] = $data['groupName'];
        $id = $this->userGroupRepository->insert($item);
        if (isset($data['roleId']) && !empty($data['roleId'])) {
            $this->insertRoleId($id, $data['roleId']);
        }
        return $id;
    }


    public function getUserGroupRole(array $groupIdArray)
    {
        return $this->userGroupRoleRepository->getRoleIdArray($groupIdArray);
    }

    public function info($id)
    {
        $info = $this->userGroupRepository->find($id);
        $userGroupRole = $this->getUserGroupRole([$id]);
        return ['info' => $info, 'userGroupRole' => $userGroupRole];
    }

    public function update(array $data)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $data['id'];
        $item['groupName'] = $data['groupName'];
        $this->userGroupRepository->update($item);
        $existRoleId = $this->userGroupRoleRepository->getRoleIdArray([$data['id']]);
        $add = array_diff($data['roleId'], $existRoleId);
        $this->insertRoleId($data['id'], $add);
        $delete = array_diff($existRoleId, $data['roleId']);
        $this->deleteRoleId($data['id'], $delete);
    }

    public function delete($id)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['isDel'] = Code::DELETED;
        $this->userGroupRepository->update($item);
    }

    private function deleteRoleId($id, array $roleIdArray)
    {
        foreach ($roleIdArray as $roleId) {
            $item = [];
            $item['groupId'] = $id;
            $item['roleId'] = $roleId;
            $this->userGroupRoleRepository->delete($item);
        }
    }

    private function insertRoleId($id, array $roleIdArray)
    {
        foreach ($roleIdArray as $roleId) {
            $item = $this->getCreateInfo();
            $item['groupId'] = $id;
            $item['roleId'] = $roleId;
            $this->userGroupRoleRepository->insert($item);
        }
    }
}