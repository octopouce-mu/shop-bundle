<?php

namespace Octopouce\ShopBundle\Repository;

use Octopouce\ShopBundle\Entity\Billing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Billing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Billing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Billing[]    findAll()
 * @method Billing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Billing::class);
    }

    // /**
    //  * @return Billing[] Returns an array of Billing objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Billing
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
