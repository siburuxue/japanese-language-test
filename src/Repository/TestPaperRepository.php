<?php

namespace App\Repository;

use App\Entity\TestPaper;
use App\Lib\Constant\Code;
use App\Lib\Constant\Dict;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @extends ServiceEntityRepository<TestPaper>
 *
 * @method TestPaper|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestPaper|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestPaper[]    findAll()
 * @method TestPaper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestPaperRepository extends ServiceEntityRepository
{
    use CommonRepositoryTrait;

    private ObjectManager $manager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestPaper::class);
        $this->manager = $registry->getManager();
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('t')->where("t.isDel = ".Code::UN_DELETE);
        if (isset($param['idList']) && !empty($param['idList'])) {
            $db->andWhere("t.changeCid = :changeCid")->setParameter('changeCid', Dict::PAPER_CREATOR_CID_MANAGER);
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("t.id", $param['idList']));
            $db->andWhere($and);
        }
        if(isset($param['title']) && !empty($param['title'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("t.title", "'%{$param['title']}%'"));
            $db->andWhere($and);
        }
        if(isset($param['difficulty']) && !empty($param['difficulty'])) {
            $db->andWhere("t.difficulty = :difficulty")->setParameter('difficulty', $param['difficulty']);
        }
        $db->orderBy("t.id","DESC");
        return $db;
    }

    public function insert(array $data)
    {
        $testPaper = new TestPaper();
        $testPaper->setUid($data['uid']);
        $testPaper->setTitle($data['title']);
        $testPaper->setCreateUser($data['createUser']);
        $testPaper->setCreateTime($data['createTime']);
        $testPaper->setUpdateUser($data['updateUser']);
        $testPaper->setUpdateTime($data['updateTime']);
        $testPaper->setTestPaperJson($data['content']);
        $testPaper->setDifficulty($data['difficulty']);
        $testPaper->setChangeCid($data['changeCid']);
        $this->manager->persist($testPaper);
        $this->manager->flush();
        return $testPaper->getId();
    }

    public function delete(array $data): void
    {
        $this->logicDelete($data);
    }

    public function update(array $data)
    {
        $paper = $this->find($data['id']);
        if(isset($data['difficulty'])){
            $paper->setDifficulty($data['difficulty']);
        }
        if(isset($data['listeningRecording'])){
            $paper->setListeningRecording($data['listeningRecording']);
        }
        $paper->setUpdateUser($data['updateUser']);
        $paper->setUpdateTime($data['updateTime']);
        $this->manager->persist($paper);
        $this->manager->flush();
        return $paper->getId();
    }

    public function info(int|string $id)
    {
        $db = $this->list(['idList' => [$id]]);
        $rs = $db->getQuery()->getArrayResult();
        return $rs[0] ?? [];
    }
}
