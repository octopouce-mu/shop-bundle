<?php

namespace Octopouce\ShopBundle\Repository;

use Octopouce\ShopBundle\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Order::class);
    }

	public function findAllCarts()
	{
		$qb = $this->createQueryBuilder('o');

		$qb->orderBy('o.createdAt', 'desc');

		return $qb->getQuery()->getResult();
	}


    public function findByStatus($user = null)
    {
        $qb = $this->createQueryBuilder('o')
	        ->andWhere('o.paymentInstruction IS NOT NULL')
	        ->andWhere('o.number IS NOT NULL');

//	        ->setParameter('status', 'success')
	    if($user) {
		    $qb->andWhere('o.user = :user')
	            ->setParameter('user', $user);
	    } else {
		    $qb->andWhere('o.user IS NOT NULL');
	    }


	    $qb->orderBy('o.number', 'desc');

	    return $qb->getQuery()->getResult();
    }

	public function lastByNumber() {
		return $this->createQueryBuilder('o')
		            ->where('o.number IS NOT NULL')
					->orderBy('o.number', 'desc')
					->setMaxResults(1)
		            ->getQuery()
		            ->getOneOrNullResult()
			;
    }

}
