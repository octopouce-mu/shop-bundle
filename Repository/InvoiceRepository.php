<?php

namespace Octopouce\ShopBundle\Repository;

use Octopouce\ShopBundle\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

	public function lastByNumber() {
		return $this->createQueryBuilder('r')
		            ->orderBy('r.number', 'desc')
		            ->setMaxResults(1)
		            ->getQuery()
		            ->getOneOrNullResult()
			;
	}
}
