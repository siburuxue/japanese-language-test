<?php

namespace App\EventListener;

use App\Lib\Constant\Permission;
use App\Lib\Constant\Route;
use App\Lib\Constant\Session;
use App\Lib\Constant\User;
use App\Service\LogService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Exception\RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RequestListener
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $router,
        private LogService $logService,
    ){}

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();
            $routeName = $request->attributes->get("_route");
            $user = $this->requestStack->getSession()->get(Session::USER_SESSION_KEY, []);
            $attribute = $request->attributes->get('_route_params', []);
            $query = $request->query->all();
            $body = $request->request->all();
            $param = array_merge($attribute, $query, $body);
            $uri = $request->getRequestUri();
            // 判断是否是后台管理页面
            if (str_starts_with($uri, Route::ADMIN_ROUTE_PREFIX)) {
                // todo zp 异步记录操作日志
                $this->logService->add($routeName, $param);
                // 如果未登陆跳转到登录页面
                if (empty($user) && !in_array($routeName, Permission::UN_LOGIN)) {
                    $loginUrl = $this->router->generate(Route::LOGIN_INDEX);
                    $event->setResponse(new RedirectResponse($loginUrl));
                } else {
                    // 判断是否有权限
                    if (!in_array($routeName, Permission::UN_LOGIN) && !in_array($user['id'], User::ADMINISTRATOR_ID)) {
                        if (!str_starts_with($routeName, '_')) {
                            if (!in_array($routeName, $user['role'])) {
                                if ($request->isXmlHttpRequest()) {
                                    $response = new JsonResponse(['msg' => '无权限'], Response::HTTP_FORBIDDEN);
                                    $event->setResponse($response);
                                } else {
                                    throw new RuntimeException("无权限", Response::HTTP_FORBIDDEN);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}