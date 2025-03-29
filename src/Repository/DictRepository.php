<?php

namespace App\Repository;

use App\Entity\Dict;
use App\Lib\Constant\Code;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Dict>
 *
 * @method Dict|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dict|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dict[]    findAll()
 * @method Dict[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dict::class);
    }

    public function list(array $param = []): \Doctrine\ORM\QueryBuilder
    {
        $db = $this->createQueryBuilder('d')->where("d.isDel = " . Code::UN_DELETE);
        if (isset($param['type'])) {
            $db->andWhere('d.type = :type')->setParameter('type', $param['type']);
        }
        if (isset($param['dKey']) && $param['dKey'] !== "") {
            $db->andWhere('d.dKey = :dKey')->setParameter('dKey', $param['dKey']);
        }
        if (isset($param['dType']) && !empty($param['dType'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->like('d.type', "'%{$param['dType']}%'"));
            $db->andWhere($and);
        }
        if (isset($param['dValue']) && !empty($param['dValue'])) {
            $and = $db->expr()->andX();
            $and->add($db->expr()->like('d.dValue', "'%{$param['dValue']}%'"));
            $db->andWhere($and);
        }
        return $db;
    }

    public function getOptionMap($key): array
    {
        $db = $this->list(['type' => $key]);
        return $db->getQuery()->getArrayResult();
    }

}
