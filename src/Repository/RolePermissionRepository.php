<?php

namespace App\Repository;

use App\Entity\RolePermission;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<RolePermission>
 *
 * @method RolePermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePermission[]    findAll()
 * @method RolePermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePermissionRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePermission::class);
        $this->manager = $registry->getManager();
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('r')->where('1 = 1');
        if(isset($param['permissionId']) && !empty($param['permissionId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("r.permissionId", $param['permissionId']));
            $db->andWhere($and);
        }
        if(isset($param['roleId']) && !empty($param['roleId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("r.roleId", $param['roleId']));
            $db->andWhere($and);
        }
        return $db->getQuery()->getArrayResult();
    }
    
    public function getRoleId(array $permissionIdArray = []): array
    {
        $rs = $this->list(['permissionId' => $permissionIdArray]);
        return array_column($rs,'roleId');
    }

    public function getPermissionId(array $roleId = []): array
    {
        $rs = $this->list(['roleId' => $roleId]);
        return array_column($rs,'permissionId');
    }

    public function insert(array $data)
    {
        $rolePermission = new RolePermission();
        $rolePermission->setRoleId($data['roleId']);
        $rolePermission->setPermissionId($data['permissionId']);
        $rolePermission->setCreateUser($data['createUser']);
        $rolePermission->setCreateTime($data['createTime']);
        $this->manager->persist($rolePermission);
        $this->manager->flush();
    }
}
