<?php

namespace App\Service;

use App\Lib\Constant\Code;
use App\Lib\Constant\Tool;
use App\Lib\Constant\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use App\Entity\Role;
use App\Entity\RolePermission;
use Symfony\Component\HttpFoundation\RequestStack;

class RoleService extends CommonService
{
    private ObjectRepository $roleRepository;
    private ObjectRepository $rolePermissionRepository;

    public function __construct(
        private ManagerRegistry $doctrine,
        private PermissionService $permissionService,
        private RequestStack $requestStack
    )
    {
        parent::__construct($requestStack);
        $this->roleRepository = $doctrine->getRepository(Role::class);
        $this->rolePermissionRepository = $doctrine->getRepository(RolePermission::class);
    }

    public function list(array $array = [], string $type = '')
    {
        if (isset($array['routeName']) && !empty($array['routeName'])) {
            $permissionIdArray = $this->permissionService->getPermissionId(['routeName' => $array['routeName']]);
            if (!empty($permissionIdArray)) {
                $roleID = $this->rolePermissionRepository->getRoleId($permissionIdArray);
                if (!empty($roleID)) {
                    $array['roleId'] = array_unique($roleID);
                }
            }
        }
        $db = $this->roleRepository->list($array);
        if($type == Tool::TYPE_ARRAY){
            return $db->getQuery()->getResult();
        }
        if($type == Tool::TYPE_JSON){
            return $db->getQuery()->getArrayResult();
        }
        return $db;
    }

    public function insertPermissionId($roleId, $permissionIdArray)
    {
        foreach ($permissionIdArray as $permissionId) {
            $item = $this->getCreateInfo();
            $item['roleId'] = $roleId;
            $item['permissionId'] = $permissionId;
            $this->rolePermissionRepository->insert($item);
        }
    }

    public function insert(array $data)
    {
        $item = $this->getCreateInfo();
        $item['roleName'] = $data['roleName'];
        $id = $this->roleRepository->insert($item);
        if (isset($data['permissionId']) && !empty($data['permissionId'])) {
            $default = $this->permissionService->getDefaultRouteId();
            $data['permissionId'] = array_diff($data['permissionId'], $default);
            $this->insertPermissionId($id, $data['permissionId']);
        }
        return $id;
    }

    public function info(int $id)
    {
        return $this->roleRepository->find($id);
    }

    public function getPermissionId(array $role)
    {
        return $this->rolePermissionRepository->getPermissionId($role);
    }

    public function update(array $data)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $data['id'];
        $item['roleName'] = $data['roleName'];
        $this->roleRepository->update($item);
        $default = $this->permissionService->getDefaultRouteId();
        $existPermissionId = $this->rolePermissionRepository->getPermissionId([$data['id']]);
        $add = array_diff($data['permissionId'], $existPermissionId, $default);
        $this->insertPermissionId($data['id'], $add);
        $delete = array_diff($existPermissionId, $data['permissionId']);
        $this->deletePermissionId($data['id'], $delete);
    }

    private function deletePermissionId(mixed $roleId, array $permissionIdArray): void
    {
        foreach ($permissionIdArray as $item) {
            $this->rolePermissionRepository->delete(['roleId' => $roleId, 'permissionId' => $item]);
        }
    }

    public function delete($id): void
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['isDel'] = Code::DELETED;
        $this->roleRepository->update($item);
    }
}