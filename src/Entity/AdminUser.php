<?php

namespace App\Entity;

use App\Repository\AdminUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'admin_user')]
#[ORM\Index(name: 'idx_username', columns: ['username'])]
#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
class AdminUser
{
    #[ORM\Column(name: "id", options: ["comment" => "自增ID"])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "username", length: 255, options: ["comment" => "用户名"])]
    private ?string $username = null;

    #[ORM\Column(name: "nickname", length: 255, options: ["comment" => "昵称", "default" => ''])]
    private ?string $nickname = '';

    #[ORM\Column(name: "password", length: 255, options: ["comment" => "密码"])]
    private ?string $password = null;

    #[ORM\Column(name: "last_login", nullable: true, options: ["comment" => "上次登录时间", "default" => 0])]
    private ?int $lastLogin = 0;

    #[ORM\Column(name: "is_del", options: ["comment" => "是否删除 0否1是", "default" => 0])]
    private ?int $isDel = 0;

    #[ORM\Column(name: "create_user", options: ["comment" => "添加人"])]
    private ?int $createUser = null;

    #[ORM\Column(name: "create_time", options: ["comment" => "添加时间"])]
    private ?int $createTime = null;

    #[ORM\Column(name: "update_time", options: ["comment" => "修改时间"])]
    private ?int $updateTime = null;

    #[ORM\Column(name: "update_user", options: ["comment" => "修改人"])]
    private ?int $updateUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getLastLogin(): ?int
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?int $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

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

    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    public function setUpdateTime(int $updateTime): static
    {
        $this->updateTime = $updateTime;

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
}
