<?php

namespace App\Controller;

use App\Lib\Constant\Dict;
use App\Service\DictService;
use App\Service\TestPaperService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestPaperController extends CommonController{

    public function index(Request $request, DictService $dictService): Response
    {
        $difficulty = $dictService->getDictMap(Dict::DICT_KEY_DIFFICULTY);
        return $this->render("admin/test-paper/index.html.twig", ['difficulty' => $difficulty]);
    }

    public function list(Request $request, PaginatorInterface $paginator, TestPaperService $testPaperService, DictService $dictService): Response
    {
        $difficultyList = $dictService->getDictMap(Dict::DICT_KEY_DIFFICULTY);
        $owner = $request->request->get("owner","");
        $difficulty = $request->request->get("difficulty","");
        $title = $request->request->get("title","");
        $page = (int)$request->request->get('page', 1);
        $limit = (int)$request->request->get('limit', 10);
        $db = $testPaperService->list(['owner' => $owner, 'difficulty' => $difficulty, 'title' => $title]);
        $pagination = $paginator->paginate($db, $page, $limit);
        return $this->render("admin/test-paper/list.html.twig", ['pagination' => $pagination, 'difficulty' => $difficultyList]);
    }

    public function insert(Request $request, TestPaperService $testPaperService): Response
    {
        $difficulty = $request->request->get("difficulty");
        $content = $request->request->get("content");
        $testPaperService->insert(['content' => $content, 'difficulty' => $difficulty]);
        return $this->insertSuccess();
    }

    public function delete(Request $request, TestPaperService $testPaperService): Response
    {
        $id = $request->query->get("id");
        $testPaperService->delete($id);
        return $this->deleteSuccess();
    }

    public function setDifficulty(Request $request, TestPaperService $testPaperService): Response
    {
        $id = $request->request->get("id");
        $difficulty = $request->request->get("difficulty");
        $testPaperService->setDifficulty($id, $difficulty);
        return $this->updateSuccess();
    }

    public function uploadListeningRecording(Request $request, TestPaperService $testPaperService): Response
    {
        $id = $request->request->get("id");
        $url = $request->request->get("ossUrl");
        $testPaperService->setListeningRecording($id, $url);
        return $this->updateSuccess();
    }

    public function deleteListeningRecording(Request $request, TestPaperService $testPaperService): Response
    {
        $id = $request->query->get("id");
        $testPaperService->setListeningRecording($id);
        return $this->deleteSuccess();
    }

    public function info(Request $request, TestPaperService $testPaperService): Response
    {
        $id = $request->query->get("id");
        $info =$testPaperService->info($id);
        return $this->render("admin/test-paper/info.html.twig", ['info' => $info]);
    }
}
     