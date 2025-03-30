<?php

namespace App\Entity;

use App\Repository\TestPaperRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'test_paper')]
#[ORM\Index(name: 'test-paper_is_del', columns: ['is_del'])]
#[ORM\Index(name: 'test-paper_uid', columns: ['uid'])]
#[ORM\Entity(repositoryClass: TestPaperRepository::class)]
class TestPaper
{
    #[ORM\Column(name: "id")]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?int $id = null;

    #[ORM\Column(name: "uid", options: ["comment" => "用户ID", "default" => 0])]
    private ?int $uid = 0;

    #[ORM\Column(name: "title", length: 255, options: ["comment" => "标题", "default" => ''])]
    private ?string $title = '';

    #[ORM\Column(name: "test_paper_json", type: Types::JSON, options: ["comment" => "题库内容json"])]
    private ?array $testPaperJson = [];

    #[ORM\Column(name: "change_cid", nullable: true, options: ["comment" => "修改人身份 0用户添加 1 后台修改", "default" => 0])]
    private ?int $changeCid = 0;

    #[ORM\Column(name: "difficulty", options: ["comment" => "难度 1简单 5困难", "default" => 1])]
    private ?int $difficulty = 1;

    #[ORM\Column(name: "listening_recording", length: 255, options: ["comment" => "听力录音", "default" => ''])]
    private ?string $listeningRecording = '';

    #[ORM\Column(name: "is_del", options: ["comment" => "是否删除 0否1是", "default" => 0])]
    private ?int $isDel = 0;

    #[ORM\Column(name: "create_user", nullable: true, options: ["comment" => "添加人", "default" => 0])]
    private ?int $createUser = 0;

    #[ORM\Column(name: "create_time", options: ["comment" => "添加时间", "default" => 0])]
    private ?int $createTime = 0;

    #[ORM\Column(name: "update_time", nullable: true, options: ["comment" => "修改时间", "default" => 0])]
    private ?int $updateTime = 0;

    #[ORM\Column(name: "update_user", nullable: true, options: ["comment" => "修改人", "default" => 0])]
    private ?int $updateUser = 0;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTestPaperJson(): ?array
    {
        return $this->testPaperJson;
    }

    public function setTestPaperJson(array $testPaperJson): static
    {
        $this->testPaperJson = $testPaperJson;

        return $this;
    }

    public function getChangeCid(): ?int
    {
        return $this->changeCid;
    }

    public function setChangeCid(?int $changeCid): static
    {
        $this->changeCid = $changeCid;

        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getListeningRecording(): ?string
    {
        return $this->listeningRecording;
    }

    public function setListeningRecording(string $listeningRecording): static
    {
        $this->listeningRecording = $listeningRecording;

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

    public function setCreateUser(?int $createUser): static
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

    public function setUpdateTime(?int $updateTime): static
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    public function getUpdateUser(): ?int
    {
        return $this->updateUser;
    }

    public function setUpdateUser(?int $updateUser): static
    {
        $this->updateUser = $updateUser;

        return $this;
    }
}
