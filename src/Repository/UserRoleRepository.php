<?php

namespace App\Repository;

use App\Entity\UserRole;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<UserRole>
 *
 * @method UserRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRole[]    findAll()
 * @method UserRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRoleRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRole::class);
        $this->manager = $registry->getManager();
    }

    public function insert(array $data)
    {
        $userRole = new UserRole();
        $userRole->setUserId($data['userId']);
        $userRole->setRoleId($data['roleId']);
        $userRole->setCreateUser($data['createUser']);
        $userRole->setCreateTime($data['createTime']);
        $this->manager->persist($userRole);
        $this->manager->flush();
    }

    public function list(array $param)
    {
        $db = $this->createQueryBuilder('u')->where("1 = 1");
        if(isset($param['userId']) && !empty($param['userId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in('u.userId',$param['userId']));
            $db->andWhere($and);
        }
        return $db->getQuery()->getArrayResult();
    }

    public function getRoleId(array $userId = []): array
    {
        $rs = $this->list(['userId' => $userId]);
        return array_column($rs, 'roleId');
    }
}
