<?php

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'log')]
#[ORM\Index(name: 'idx_uid', columns: ['uid'])]
#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Column(name: "id", options: ["comment" => "自增主键"])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "uid", options: ["comment" => "操作人", "default" => 0])]
    private ?int $uid = 0;

    #[ORM\Column(name: "username", length: 255, nullable: true, options: ["comment" => "用户名", "default" => ''])]
    private ?string $username = '';

    #[ORM\Column(name: "content", type: Types::TEXT, nullable: true, options: ["comment" => "行为"])]
    private ?string $content = null;

    #[ORM\Column(name: "param", type: Types::JSON, nullable: true)]
    private ?array $param = null;

    #[ORM\Column(name: "create_time", nullable: true, options: ["comment" => "创建时间", "default" => 0])]
    private ?int $createTime = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getParam(): ?array
    {
        return $this->param;
    }

    public function setParam(?array $param): static
    {
        $this->param = $param;

        return $this;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(?int $createTime): static
    {
        $this->createTime = $createTime;

        return $this;
    }
}
