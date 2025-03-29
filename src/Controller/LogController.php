<?php

namespace App\Controller;

use App\Service\LogService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogController extends CommonController
{
    public function index(): Response
    {
        return $this->render('admin/log/index.html.twig');
    }

    public function list(Request $request, LogService $logService, PaginatorInterface $paginator): Response
    {
        $page = $request->request->get('page', 1);
        $limit = $request->request->get('limit', 10);
        $uid = $request->request->get('uid');
        $username = $request->request->get('username');
        $content = $request->request->get('content');
        $start = $request->request->get('start');
        $end = $request->request->get('end');
        $db = $logService->list([
            'uid' => $uid,
            'username' => $username,
            'content' => $content,
            'start' => $start,
            'end' => $end,
        ]);
        $pagination = $paginator->paginate($db, $page, $limit);
        return $this->render('admin/log/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}