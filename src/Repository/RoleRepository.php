<?php

namespace App\Repository;

use App\Entity\Role;
use App\Lib\Constant\Code;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
        $this->manager = $registry->getManager();
    }

    public function list(array $param): \Doctrine\ORM\QueryBuilder
    {
        $db = $this->createQueryBuilder('r')->where("r.isDel = ".Code::UN_DELETE);
        if(isset($param['roleName']) && !empty($param['roleName'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("r.roleName", "'%{$param['roleName']}%'"));
            $db->andWhere($and);
        }
        if(isset($param['roleId']) && !empty($param['roleId'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("r.id", $param['roleId']));
            $db->andWhere($and);
        }
        $db->orderBy("r.id","DESC");
        return $db;
    }

    public function insert(array $data)
    {
        $role = new Role();
        $role->setRoleName($data['roleName']);
        $role->setCreateUser($data['createUser']);
        $role->setCreateTime($data['createTime']);
        $role->setUpdateUser($data['updateUser']);
        $role->setUpdateTime($data['updateTime']);
        $this->manager->persist($role);
        $this->manager->flush();
        return $role->getId();
    }

    public function update(array $data)
    {
        $role = $this->find($data['id']);
        if(isset($data['roleName'])){
            $role->setRoleName($data['roleName']);
        }
        if(isset($data['isDel'])){
            $role->setIsDel($data['isDel']);
        }
        $role->setUpdateUser($data['updateUser']);
        $role->setUpdateTime($data['updateTime']);
        $this->manager->persist($role);
        $this->manager->flush();
    }
}
