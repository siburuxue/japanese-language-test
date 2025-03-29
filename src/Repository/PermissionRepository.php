<?php

namespace App\Repository;

use App\Entity\Permission;
use App\Lib\Constant\Code;
use App\Lib\Constant\Permission as PermissionConstant;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Permission>
 *
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;


    use CommonRepositoryTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
        $this->manager = $registry->getManager();
    }

    public function insert(array $route)
    {
        $permission = new Permission();
        $permission->setRouteName($route['routeName']);
        $permission->setRoute($route['route']);
        $permission->setParentId($route['parentId']);
        $permission->setGroupName($route['groupName']);
        $permission->setIconName($route['iconName']);
        $permission->setIsMenu($route['isMenu']);
        $permission->setIsDefault($route['isDefault']);
        $permission->setCreateUser($route['createUser']);
        $permission->setCreateTime($route['createTime']);
        $permission->setUpdateUser($route['updateUser']);
        $permission->setUpdateTime($route['updateTime']);
        $this->manager->persist($permission);
        $this->manager->flush();
        return $permission->getId();
    }

    public function deleteAll(array $data): void
    {
        foreach ($data as $index => $datum) {
            if(is_array($datum)){
                foreach ($datum as $item) {
                    $this->delete([$index => $item]);
                }
            }else{
                $this->delete([$index => $datum]);
            }
        }
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('p')
                ->where("p.isDel = ".Code::UN_DELETE);
        if(isset($param['isDefault'])){
            $db->andWhere("p.isDefault = :isDefault");
            $db->setParameter('isDefault', $param['isDefault']);
        }
        if(isset($param['parentId'])){
            $db->andWhere("p.parentId = :parentId");
            $db->setParameter('parentId', $param['parentId']);
        }
        if(isset($param['isMenu'])){
            $db->andWhere("p.isMenu = :isMenu");
            $db->setParameter('isMenu', $param['isMenu']);
        }
        if(isset($param['route'])){
            if(is_string($param['route'])){
                $db->andWhere("p.route = :route");
                $db->setParameter('route', $param['route']);
            }else if(is_array($param['route'])){
                if(!empty($param['route'])){
                    $and = $db->expr()->andX();
                    $and->add($db->expr()->in("p.route", $param['route']));
                    $db->andWhere($and);
                }
            }
        }
        if(isset($param['routeName'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("p.routeName", "'%{$param['routeName']}%'"));
            $db->andWhere($and);
        }
        if(isset($param['id']) && !empty($param['id'])){
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("p.id", $param['id']));
            $db->andWhere($and);
        }
        return $db->getQuery()->getArrayResult();
    }

    public function getId(array $param = []):array
    {
        $list = $this->list(['routeName' => $param['routeName'],'isDefault' => PermissionConstant::UN_DEFAULT_PERMISSION]);
        return array_column($list,'id');
    }

    public function getRoute(array $idArray = []): array
    {
        $list = $this->list(['id' => $idArray,'isDefault' => PermissionConstant::UN_DEFAULT_PERMISSION]);
        return array_column($list,'route');
    }

    public function getRouteName(array $idArray = []): array
    {
        $list = $this->list(['id' => $idArray,'isDefault' => PermissionConstant::UN_DEFAULT_PERMISSION]);
        return array_column($list,'routeName');
    }

    public function default()
    {
        return $this->list(['isDefault' => PermissionConstant::DEFAULT_PERMISSION]);
    }
}
