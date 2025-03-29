<?php

namespace App\Repository;

use App\Entity\UserGroupRole;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<UserGroupRole>
 *
 * @method UserGroupRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroupRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroupRole[]    findAll()
 * @method UserGroupRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGroupRoleRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroupRole::class);
        $this->manager = $registry->getManager();
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('g')->where("1 = 1");
        if(isset($param['groupId']) && !empty($param['groupId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("g.groupId", $param['groupId']));
            $db->andWhere($and);
        }
        return $db->getQuery()->getArrayResult();
    }
    
    public function getRoleIdArray(array $groupIdArray = []): array
    {
        $rs = $this->list(['groupId' => $groupIdArray]);
        return array_column($rs,'roleId');
    }

    public function getGroupIdArray(array $roleId = []): array
    {
        $rs = $this->list(['roleId' => $roleId]);
        return array_column($rs,'groupId');
    }

    public function insert(array $data)
    {
        $item = new UserGroupRole();
        $item->setGroupId($data['groupId']);
        $item->setRoleId($data['roleId']);
        $item->setCreateUser($data['createUser']);
        $item->setCreateTime($data['createTime']);
        $this->manager->persist($item);
        $this->manager->flush();
    }
}
