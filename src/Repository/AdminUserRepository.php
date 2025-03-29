<?php

namespace App\Repository;

use App\Entity\AdminUser;
use App\Lib\Constant\Code;
use App\Repository\Common\CommonRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @extends ServiceEntityRepository<AdminUser>
 *
 * @method AdminUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminUser[]    findAll()
 * @method AdminUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminUserRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;

    use CommonRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminUser::class);
        $this->manager = $registry->getManager();
    }

    public function getInfoByUsername(string $username)
    {
        $db = $this->list(['username' => $username]);
        return $db->getQuery()->getArrayResult();
    }

    public function isExist(string $username): bool
    {
        $rs = $this->getInfoByUsername($username);
        return count($rs) > 0;
    }

    public function list(array $param = [])
    {
        $db = $this->createQueryBuilder('u')
            ->select("u.id,u.username,u.nickname,u.password,p.groupName")
            ->leftJoin("App\\Entity\\UserGroupUser", "pu", \Doctrine\ORM\Query\Expr\Join::WITH, "u.id = pu.userId")
            ->leftJoin("App\\Entity\\UserGroup", "p", \Doctrine\ORM\Query\Expr\Join::WITH, "pu.groupId = p.id")
            ->where("u.isDel = " . Code::UN_DELETE);
        if (isset($param['username']) && !empty($param['username'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("u.username", "'%{$param['username']}%'"));
            $db->andWhere($and);
        }
        if (isset($param['nickname']) && !empty($param['nickname'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->like("u.nickname", "'%{$param['nickname']}%'"));
            $db->andWhere($and);
        }
        if (isset($param['id']) && !empty($param['id'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->in("u.id", $param['id']));
            $db->andWhere($and);
        }
        $db->orderBy("u.id", "DESC");
        return $db;
    }

    public function insert(array $data)
    {
        $user = new AdminUser();
        $user->setUsername($data['username']);
        $user->setNickname($data['nickname']);
        $user->setPassword($data['password']);
        $user->setCreateUser($data['createUser']);
        $user->setCreateTime($data['createTime']);
        $user->setUpdateUser($data['updateUser']);
        $user->setUpdateTime($data['updateTime']);
        $this->manager->persist($user);
        $this->manager->flush();
        return $user->getId();
    }

    public function lastLogin($data)
    {
        $user = $this->find($data['id']);
        $user->setLastLogin($data['lastLogin']);
        $user->setUpdateTime($data['updateTime']);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function delete(array $data)
    {
        $user = $this->find($data['id']);
        $user->setIsDel($data['isDel']);
        $user->setUpdateUser($data['updateUser']);
        $user->setUpdateTime($data['updateTime']);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function update(array $data)
    {
        $user = $this->find($data['id']);
        $user->setUsername($data['username']);
        $user->setNickname($data['nickname']);
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        $user->setUpdateUser($data['updateUser']);
        $user->setUpdateTime($data['updateTime']);
        $this->manager->persist($user);
        $this->manager->flush();
        return $user->getId();
    }
}
