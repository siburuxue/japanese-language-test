<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    private ObjectManager $manager;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
        $this->manager = $registry->getManager();
    }

    public function insert($item)
    {
        $log = new Log();
        $log->setUid($item['uid']);
        $log->setUsername($item['username']);
        $log->setContent($item['content']);
        $log->setParam($item['param']);
        $log->setCreateTime($item['createTime']);
        $this->manager->clear();
        $this->manager->persist($log);
        $this->manager->flush();
        return $log->getId();
    }

    public function list($param)
    {
        $db = $this->createQueryBuilder('l')->where('1 = 1');
        if(isset($param['uid']) && !empty($param['uid'])){
            $db->andWhere("l.uid = :uid");
            $db->setParameter('uid', $param['uid']);
        }
        if(isset($param['username']) && !empty($param['username'])){
            $db->andWhere("l.username = :username");
            $db->setParameter('username', $param['username']);
        }
        if(isset($param['start']) && !empty($param['start'])){
            $db->andWhere("l.createTime >= :start");
            $db->setParameter('start', strtotime($param['start']));
        }
        if(isset($param['end']) && !empty($param['end'])){
            $db->andWhere("l.createTime <= :end");
            $db->setParameter('end', strtotime($param['end']));
        }
        $db->orderBy("l.id","DESC");
        return $db;
    }
}
