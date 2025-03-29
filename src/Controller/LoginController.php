<?php

namespace App\Controller;

use App\Lib\Constant\Code;
use App\Lib\Constant\Message;
use App\Lib\Constant\Route;
use App\Lib\Constant\Session;
use App\Lib\Constant\Tool;
use App\Service\AdminUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends CommonController
{
    public function index(): Response
    {
        return $this->render('admin/login/index.html.twig');
    }

    public function submit(Request $request, AdminUserService $service): JsonResponse
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');
        if ($username === '' || $password === '') {
            return $this->json(['status' => Code::RESPONSE_FALSE, 'msg' => Message::LOGIN_ERROR_MSG]);
        }
        $rs = $service->loginCheck($username, $password);
        $msg = $rs ? Message::LOGIN_SUCCESS_MSG : Message::LOGIN_ERROR_MSG;
        return $this->json(['status' => $rs, 'msg' => $msg]);
    }

    public function logOut(RequestStack $requestStack): RedirectResponse
    {
        $session = $requestStack->getSession();
        $session->remove(Session::USER_SESSION_KEY);
        $session->remove(Session::MENU_SESSION_KEY);
        return $this->redirectToRoute(Route::LOGIN_INDEX);
    }
}