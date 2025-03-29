<?php

namespace App\Entity;

use App\Repository\DictRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'dict')]
#[ORM\Index(name: 'idx_type_key', columns: ['type', 'd_key'])]
#[ORM\Index(name: 'idx_u_key', columns: ['u_key'])]
#[ORM\Index(name: 'idx_d_value', columns: ['d_value'])]
#[ORM\Entity(repositoryClass: DictRepository::class)]
class Dict
{
    #[ORM\Column(name: "id", options: ["comment" => " 主键 "])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "type", length: 255, options: ["comment" => "字典名称"])]
    private ?string $type = null;

    #[ORM\Column(name: "d_key", length: 255)]
    private ?string $dKey = null;

    #[ORM\Column(name: "d_value", length: 255)]
    private ?string $dValue = null;

    #[ORM\Column(name: "u_key", length: 255, options: ["comment" => "唯一标识"])]
    private ?string $uKey = null;

    #[ORM\Column(name: "remark", length: 255, options: ["comment" => "备注"])]
    private ?string $remark = null;

    #[ORM\Column(name: "is_del", nullable: true, options: ["comment" => "0否1是"])]
    private ?int $isDel = null;

    #[ORM\Column(name: "create_user", options: ["comment" => " 添加人 "])]
    private ?int $createUser = null;

    #[ORM\Column(name: "update_user", options: ["comment" => " 修改人 "])]
    private ?int $updateUser = null;

    #[ORM\Column(name: "create_time", options: ["comment" => " 添加时间 "])]
    private ?int $createTime = null;

    #[ORM\Column(name: "update_time", options: ["comment" => " 修改时间 "])]
    private ?int $updateTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDKey(): ?string
    {
        return $this->dKey;
    }

    public function setDKey(string $dKey): static
    {
        $this->dKey = $dKey;

        return $this;
    }

    public function getDValue(): ?string
    {
        return $this->dValue;
    }

    public function setDValue(string $dValue): static
    {
        $this->dValue = $dValue;

        return $this;
    }

    public function getUKey(): ?string
    {
        return $this->uKey;
    }

    public function setUKey(string $uKey): static
    {
        $this->uKey = $uKey;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): static
    {
        $this->remark = $remark;

        return $this;
    }

    public function getIsDel(): ?int
    {
        return $this->isDel;
    }

    public function setIsDel(?int $isDel): static
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

    public function getUpdateUser(): ?int
    {
        return $this->updateUser;
    }

    public function setUpdateUser(int $updateUser): static
    {
        $this->updateUser = $updateUser;

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
}
