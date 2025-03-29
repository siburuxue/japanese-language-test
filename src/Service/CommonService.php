<?php

namespace App\Service;

use App\Lib\Constant\Session;
use App\Lib\Constant\User;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CommonService
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    protected function getSessionUser()
    {
        return $this->requestStack->getSession()->get(Session::USER_SESSION_KEY, []);
    }

    public function __get($name)
    {
        return match ($name) {
            "user" => $this->getSessionUser(),
            default => null
        };
    }

    #[ArrayShape([
        'createUser' => "mixed",
        'updateUser' => "mixed",
        'createTime' => "int",
        'updateTime' => "int"
    ])]
    protected function getCreateInfo(): array
    {
        return [
            'createUser' => $this->user['id'],
            'updateUser' => $this->user['id'],
            'createTime' => time(),
            'updateTime' => time(),
        ];
    }

    #[ArrayShape(['updateUser' => "mixed", 'updateTime' => "int"])]
    protected function getUpdateInfo(): array
    {
        return [
            'updateUser' => $this->user['id'],
            'updateTime' => time(),
        ];
    }

    protected function setUpdateInfo(&$item): void
    {
        $item['updateUser'] = $this->user['id'];
        $item['updateTime'] = time();
    }

    protected function setCreateInfo(&$item): void
    {
        $item['createUser'] = $this->user['id'];
        $item['updateUser'] = $this->user['id'];
        $item['createTime'] = time();
        $item['updateTime'] = time();
    }
}