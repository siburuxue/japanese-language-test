<?php

namespace App\Entity;

use App\Repository\RolePermissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'role_permission')]
#[ORM\Index(name: 'idx_role_id', columns: ['role_id'])]
#[ORM\Index(name: 'idx_permission_id', columns: ['permission_id'])]
#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
class RolePermission
{
    #[ORM\Column(name: "id", options: ["comment" => "自增主键"])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "role_id", options: ["comment" => "规则ID", "default" => 0])]
    private ?int $roleId = 0;

    #[ORM\Column(name: "permission_id", options: ["comment" => "权限ID", "default" => 0])]
    private ?int $permissionId = 0;

    #[ORM\Column(name: "create_user", options: ["comment" => "创建人", "default" => 0])]
    private ?int $createUser = 0;

    #[ORM\Column(name: "create_time", options: ["comment" => "创建时间", "default" => 0])]
    private ?int $createTime = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): static
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getPermissionId(): ?int
    {
        return $this->permissionId;
    }

    public function setPermissionId(int $permissionId): static
    {
        $this->permissionId = $permissionId;

        return $this;
    }

    public function getCreateUser(): ?int
    {
        return $this->createUser;
    }

    public function setCreateUser(int $createUser): static
    {
        $this->createUser = $createUser;

        return $this;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(int $createTime): static
    {
        $this->createTime = $createTime;

        return $this;
    }
}
