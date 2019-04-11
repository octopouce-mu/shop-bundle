<?php

namespace Octopouce\ShopBundle\Repository;

use Octopouce\ShopBundle\Entity\OrderState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderState|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderState|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderState[]    findAll()
 * @method OrderState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderStateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderState::class);
    }

    // /**
    //  * @return OrderState[] Returns an array of OrderState objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderState
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
