<?php

namespace App\Controller;

use App\Lib\Constant\Tool;
use App\Service\DictService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DictController extends CommonController
{
    public function index(): Response
    {
        return $this->render('admin/dict/index.html.twig');
    }

    public function list(Request $request, DictService $dictService, PaginatorInterface $paginator): Response
    {
        if (!$this->csrfValid()) {
            return new Response(Tool::CSRF_ERROR);
        }
        $page = $request->request->get('page',1);
        $limit = $request->request->get('limit',10);
        $type = $request->request->get('type','');
        $dKey = $request->request->get('dKey','');
        $dValue = $request->request->get('dValue','');
        $db = $dictService->list([
            'dType' => $type,
            'dKey' => $dKey,
            'dValue' => $dValue,
        ]);
        $pagination = $paginator->paginate($db,$page,$limit);
        return $this->render('admin/dict/list.html.twig',[
            'pagination' => $pagination
        ]);
    }
}