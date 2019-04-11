<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Octopouce\ShopBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderSessionStorage implements OrderStorageInterface
{
	private const ORDER_KEY_NAME = 'orderId';

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
	{
		$this->entityManager = $entityManager;
		$this->session = $session;
	}

	public function set(string $orderId): void
	{
		$this->session->set(self::ORDER_KEY_NAME, $orderId);
	}

	public function remove(): void
	{
		$this->session->remove(self::ORDER_KEY_NAME);
	}

	public function getOrderById(): ?Order
	{
		if ($this->has()) {
			return $this->entityManager->getRepository(Order::class)->find($this->get());
		}
		return null;
	}

	public function has(): bool
	{
		return $this->session->has(self::ORDER_KEY_NAME);
	}

	public function get(): string
	{
		return $this->session->get(self::ORDER_KEY_NAME);
	}
}
