<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'permission')]
#[ORM\Index(name: 'idx_route', columns: ['route'])]
#[ORM\Index(name: 'idx_is_menu', columns: ['is_menu'])]
#[ORM\Index(name: 'idx_is_default', columns: ['is_default'])]
#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    #[ORM\Column(name: "id", options: ["comment" => "主键ID自增"])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "route", length: 255, options: ["comment" => "路由名字", "default" => ''])]
    private ?string $route = '';

    #[ORM\Column(name: "route_name", length: 255, options: ["comment" => "路由文言", "default" => ''])]
    private ?string $routeName = '';

    #[ORM\Column(name: "parent_id", options: ["comment" => "父ID", "default" => 0])]
    private ?int $parentId = 0;

    #[ORM\Column(name: "group_name", length: 255, options: ["comment" => "分组名", "default" => ''])]
    private ?string $groupName = '';

    #[ORM\Column(name: "icon_name", length: 255, options: ["comment" => "css图标", "default" => ''])]
    private ?string $iconName = '';

    #[ORM\Column(name: "is_menu", options: ["comment" => "是否菜单", "default" => 0])]
    private ?int $isMenu = 0;

    #[ORM\Column(name: "is_default", options: ["comment" => "默认权限", "default" => 0])]
    private ?int $isDefault = 0;

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

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName): static
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): static
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): static
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getIconName(): ?string
    {
        return $this->iconName;
    }

    public function setIconName(string $iconName): static
    {
        $this->iconName = $iconName;

        return $this;
    }

    public function getIsMenu(): ?int
    {
        return $this->isMenu;
    }

    public function setIsMenu(int $isMenu): static
    {
        $this->isMenu = $isMenu;

        return $this;
    }

    public function getIsDefault(): ?int
    {
        return $this->isDefault;
    }

    public function setIsDefault(int $isDefault): static
    {
        $this->isDefault = $isDefault;

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
