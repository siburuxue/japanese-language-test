<?php

namespace App\Repository;

use App\Entity\UserGroup;
use App\Lib\Constant\Code;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<UserGroup>
 *
 * @method UserGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroup[]    findAll()
 * @method UserGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGroupRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
        $this->manager = $registry->getManager();
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('u')->where('u.isDel = '.Code::UN_DELETE);
        if(isset($param['groupName'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("u.groupName", "'%{$param['groupName']}%'"));
            $db->andWhere($and);
        }
        if(isset($param['groupId']) && !empty($param['groupId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("u.id", $param['groupId']));
            $db->andWhere($and);
        }
        $db->orderBy("u.id","DESC");
        return $db;
    }

    public function insert(array $data)
    {
        $userGroup = new UserGroup();
        $userGroup->setGroupName($data['groupName']);
        $userGroup->setCreateUser($data['createUser']);
        $userGroup->setCreateTime($data['createTime']);
        $userGroup->setUpdateUser($data['updateUser']);
        $userGroup->setUpdateTime($data['updateTime']);
        $this->manager->persist($userGroup);
        $this->manager->flush();
        return $userGroup->getId();
    }

    public function update(array $data)
    {
        $userGroup = $this->find($data['id']);
        if(isset($data['groupName'])){
            $userGroup->setGroupName($data['groupName']);
        }
        if(isset($data['isDel'])){
            $userGroup->setIsDel($data['isDel']);
        }
        $userGroup->setUpdateUser($data['updateUser']);
        $userGroup->setUpdateTime($data['updateTime']);
        $this->manager->persist($userGroup);
        $this->manager->flush();
    }
}
