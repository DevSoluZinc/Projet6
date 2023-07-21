<?php

namespace App\Repository;

use App\Entity\FigureId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FigureId>
 *
 * @method FigureId|null find($id, $lockMode = null, $lockVersion = null)
 * @method FigureId|null findOneBy(array $criteria, array $orderBy = null)
 * @method FigureId[]    findAll()
 * @method FigureId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FigureIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FigureId::class);
    }

//    /**
//     * @return FigureId[] Returns an array of FigureId objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FigureId
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
