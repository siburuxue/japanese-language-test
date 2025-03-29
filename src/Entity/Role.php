<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'role')]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Column(name: "id", options: ["comment" => "自增主键"])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "role_name", length: 255, options: ["comment" => "规则名字", "default" => ''])]
    private ?string $roleName = '';

    #[ORM\Column(name: "is_del", options: ["comment" => "0否1是", "default" => 0])]
    private ?int $isDel = 0;

    #[ORM\Column(name: "create_user", options: ["comment" => "创建人", "default" => 0])]
    private ?int $createUser = 0;

    #[ORM\Column(name: "create_time", options: ["comment" => "创建时间", "default" => 0])]
    private ?int $createTime = 0;

    #[ORM\Column(name: "update_user", options: ["comment" => "修改人", "default" => 0])]
    private ?int $updateUser = 0;

    #[ORM\Column(name: "update_time", options: ["comment" => "修改时间", "default" => 0])]
    private ?int $updateTime = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): static
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getIsDel(): ?int
    {
        return $this->isDel;
    }

    public function setIsDel(int $isDel): static
    {
        $this->isDel = $isDel;

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

    public function getUpdateUser(): ?int
    {
        return $this->updateUser;
    }

    public function setUpdateUser(int $updateUser): static
    {
        $this->updateUser = $updateUser;

        return $this;
    }

    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    public function setUpdateTime(int $updateTime): static
    {
        $this->updateTime = $updateTime;

        return $this;
    }
}
