<?php

namespace App\Service;

use App\Lib\Constant\Code;
use App\Lib\Constant\Dict;
use Symfony\Component\HttpFoundation\RequestStack;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use App\Entity\TestPaper;

class TestPaperService extends CommonService
{
    private ObjectRepository $testPaperRepository;

    public function __construct(RequestStack             $requestStack,
                                ManagerRegistry          $doctrine,
                                private AdminUserService $adminUserService,
    )
    {
        parent::__construct($requestStack);

        $this->testPaperRepository = $doctrine->getRepository(TestPaper::class);
    }

    public function list(array $params = [])
    {
        if(isset($params['owner']) && !empty($params['owner'])){
            $ids = $this->adminUserService->getIdsByNickname($params['owner']);
            if(!empty($ids)){
                $params['idList'] = $ids;
            }
        }
        return $this->testPaperRepository->list($params);
    }

    public function getCreator(int $id): array
    {
        $paper = $this->testPaperRepository->find($id);
        $name = "";
        if ($paper->getChangeCid() == Dict::PAPER_CREATOR_CID_MANAGER) {
            $adminUser = $this->adminUserService->getUserInfo($paper->getUid());
            $name = $adminUser->getNickname() . " (后台)";
        }
        return [
            'uid' => $paper->getUid(),
            'name' => $name,
        ];
    }

    public function insert(array $data)
    {
        $data['content'] = json_decode($data['content'], 1);
        $item = $this->getCreateInfo() + $data;
        $item['uid'] = $this->user['id'];
        $item['changeCid'] = Dict::PAPER_CREATOR_CID_MANAGER;
        $item['title'] = $data['content']['output']['exam_info']['title'];
        return $this->testPaperRepository->insert($item);
    }

    public function delete(int|string $id)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['isDel'] = Code::DELETED;
        $this->testPaperRepository->delete($item);
    }

    public function setDifficulty(int|string $id, int|string $difficulty)
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['difficulty'] = $difficulty;
        $this->testPaperRepository->update($item);
    }

    public function setListeningRecording(int|string $id, string $url = "")
    {
        $item = $this->getUpdateInfo();
        $item['id'] = $id;
        $item['listeningRecording'] = $url;
        $this->testPaperRepository->update($item);
    }

    public function info(int|string $id)
    {
        return $this->testPaperRepository->info($id);
    }
}