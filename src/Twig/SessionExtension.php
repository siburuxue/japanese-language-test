<?php

namespace App\Twig;

use App\Lib\Constant\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionExtension extends \Twig\Extension\AbstractExtension
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("getUsername",[$this,'username']),
            new \Twig\TwigFunction("getNickname",[$this,'nickname']),
            new \Twig\TwigFunction("getUserId",[$this,'id']),
        ];
    }

    public function username()
    {
        $session = $this->requestStack->getSession();
        $user = $session->get(Session::USER_SESSION_KEY, []);
        return $user['username'];
    }

    public function nickname()
    {
        $session = $this->requestStack->getSession();
        $user = $session->get(Session::USER_SESSION_KEY, []);
        return $user['nickname'];
    }

    public function id()
    {
        $session = $this->requestStack->getSession();
        $user = $session->get(Session::USER_SESSION_KEY, []);
        return $user['id'];
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}