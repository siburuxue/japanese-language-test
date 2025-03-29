<?php

namespace App\Repository;

use App\Entity\UserGroupUser;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<UserGroupUser>
 *
 * @method UserGroupUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroupUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroupUser[]    findAll()
 * @method UserGroupUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGroupUserRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroupUser::class);
        $this->manager = $registry->getManager();
    }

    public function insert(array $data)
    {
        $userGroupUser = new UserGroupUser();
        $userGroupUser->setUserId($data['userId']);
        $userGroupUser->setGroupId($data['groupId']);
        $userGroupUser->setCreateUser($data['createUser']);
        $userGroupUser->setCreateTime($data['createTime']);
        $this->manager->persist($userGroupUser);
        $this->manager->flush();
    }

    public function update(array $data)
    {
        $userGroupUser = $this->findOneBy(['userId' => $data['userId']]);
        if(!empty($userGroupUser)){
            $userGroupUser->setGroupId($data['groupId']);
            $this->manager->persist($userGroupUser);
            $this->manager->flush();
            return true;
        }
        return false;
    }

    public function list(array $param)
    {
        $db = $this->createQueryBuilder('u')->where("1 = 1");
        if(isset($param['groupId']) && !empty($param['groupId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in('u.groupId',$param['groupId']));
            $db->andWhere($and);
        }
        if(isset($param['userId']) && !empty($param['userId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in('u.userId',$param['userId']));
            $db->andWhere($and);
        }
        return $db->getQuery()->getArrayResult();
    }

    public function getUserId(array $userGroupId = []): array
    {
        $rs = $this->list(['groupId' => $userGroupId]);
        return array_column($rs,'userId');
    }

    public function getGroupId(array $userId = []): array
    {
        $rs = $this->list(['userId' => $userId]);
        return array_column($rs,'groupId');
    }
}
