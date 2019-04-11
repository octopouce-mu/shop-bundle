<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Factory;

use App\Entity\Account\User;
use Octopouce\ShopBundle\Entity\Billing;
use Octopouce\ShopBundle\Entity\Discount;
use Octopouce\ShopBundle\Entity\Order;
use Octopouce\ShopBundle\Entity\OrderItem;
use Octopouce\ShopBundle\Entity\OrderState;
use Octopouce\ShopBundle\Entity\Shipment;
use Octopouce\ShopBundle\Entity\State;
use Octopouce\ShopBundle\Factory\Order\Event\OrderEvents;
use Octopouce\ShopBundle\Factory\Order\Storage\OrderSessionStorage;
use Octopouce\ShopBundle\Factory\Order\Storage\OrderStorageInterface;
use Octopouce\ShopBundle\Factory\Order\Summary;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrderFactory
{
	/**
	 * @var OrderStorageInterface
	 */
	private $storage;

	/**
	 * @var Order
	 */
	private $order;

	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;

	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;


	public function __construct(
		OrderSessionStorage $storage,
		EntityManagerInterface $em,
		EventDispatcherInterface $eventDispatcher,
		TokenStorageInterface $tokenStorage
	) {
		$this->storage         = $storage;
		$this->em              = $em;
		$this->eventDispatcher = $eventDispatcher;
		$this->tokenStorage    = $tokenStorage;
		$this->order           = $this->getCurrent();
	}

	/**
	 * @return Order
	 */
	public function getCurrent(): Order {
		$order = $this->storage->getOrderById();

		if ( !$order ) {
			$order = new Order();
		}

		if(!$order->getUser() && $this->tokenStorage->getToken()) {
			$user = $this->tokenStorage->getToken()->getUser();
			if($user instanceof User) {
				$this->setUser($user);
			}
		}

		return $order;
	}

	public function setOrder(Order $order): self
	{
		$this->order = $order;
		$this->setUser($order->getUser());

		return $this;
	}

	public function setUser(User $user) {
		if($this->order) {
			$this->order->setUser($user);

			// Run events
			$event = new GenericEvent( $this->order );
			$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
			$this->em->flush();
		}

	}

	/**
	 * @param $product
	 * @param int $quantity
	 */
	public function addItem( $product, $name, int $quantity, $digital = false): void {
		$orderBeforeId = $this->order->getId();

		// check if product isn't in a cart for create new item else add a quantity
		if ( ! $this->containsProduct( $product ) ) {
			$orderItem = new OrderItem();
			$orderItem->setName( $name);
			$orderItem->setOrder( $this->order );
			$orderItem->setQuantity( $quantity );
			$orderItem->setDigital( $digital );
			$this->setArticle($product, $orderItem);
			$this->order->addItem( $orderItem );
		} elseif($this->items()) {
			$key      = $this->indexOfProduct( $product );
			$item     = $this->items()->get( $key );
			$quantity = $item->getQuantity() + 1;
			$this->setItemQuantity( $item, $quantity );
		}
		$this->em->persist( $this->order );


		// Run events
		$event = new GenericEvent( $this->order );
		$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
		$this->em->flush();

		if ( $orderBeforeId === null ) {
			$event = new GenericEvent( $this->order );
			$this->eventDispatcher->dispatch( OrderEvents::ORDER_CREATED, $event );
		}
	}

	public function getArticle( OrderItem $item ) {
		if($article = $item->getArticleLinked()) {
			return $article;
		} elseif($item->getClassLinked() && $item->getIdLinked()) {
			$article = $this->em->getRepository($item->getClassLinked())->find($item->getIdLinked());
			if($article) {
				$item->getArticleLinked($article);
				return $article;
			}
		}


		return null;
	}

	public function setArticle(OrderItem $item, $article) {
		$item->setClassLinked($this->em->getMetadataFactory()->getMetadataFor(get_class($article))->getName());
		$item->setIdLinked($article->getId());
		$item->setArticleLinked($article);
	}

	/**
	 * @param $product
	 *
	 * @return bool
	 */
	public function containsProduct( $product ): bool {
		if(!$this->items()) {
			return false;
		}

		foreach ( $this->items() as $item ) {
			if ( $item === $product || $this->getArticle($item) === $product ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $product
	 *
	 * @return int|null
	 */
	public function indexOfProduct( $product ): ?int {
		foreach ( $this->items() AS $key => $item ) {
			if ( $item === $product || $this->getArticle($item) === $product ) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setItemQuantity( OrderItem $item, int $quantity ): void {
		if ( $this->order && $this->items() && $this->items()->contains( $item ) ) {
			$key = $this->items()->indexOf( $item );
			$item->setQuantity( $quantity );
			$this->items()->set( $key, $item );
			// Run events
			$event = new GenericEvent( $this->order );
			$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
			$this->em->persist( $this->order );
			$this->em->flush();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeItemQuantity( OrderItem $item): void {
		if ( $this->order && $this->items() && $this->items()->contains( $item ) ) {
			if($item->getQuantity() === 1) {
				$this->removeItem($item);
			} else {
				$key = $this->items()->indexOf( $item );

				$item->setQuantity( $item->getQuantity() - 1 );

				$this->items()->set( $key, $item );
				// Run events
				$event = new GenericEvent( $this->order );
				$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
				$this->em->persist( $this->order );
				$this->em->flush();
			}

		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function addItemQuantity( OrderItem $item): void {
		if ( $this->order && $this->items() && $this->items()->contains( $item ) ) {
			$key = $this->items()->indexOf( $item );

			$item->setQuantity( $item->getQuantity() + 1 );

			$this->items()->set( $key, $item );
			// Run events
			$event = new GenericEvent( $this->order );
			$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
			$this->em->persist( $this->order );
			$this->em->flush();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeItem( OrderItem $item): void {

		$item->getOrder()->removeItem( $item );

		// Run events
		$event = new GenericEvent( $item->getOrder() );
		$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
		$this->em->persist( $item->getOrder() );
		$this->em->flush();
	}

	/**
	 * {@inheritdoc}
	 */
	public function items(): ?Collection {
		return $this->order->getItems();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDiscount( Discount $discount ): void {
		if ( $this->order ) {
			$this->order->setDiscount( $discount );
			// Run events
			$event = new GenericEvent( $this->order );
			$this->eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
			$this->em->persist( $this->order );
			$this->em->flush();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): void {
		$this->em->remove( $this->order );
		$this->em->flush();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEmpty(): bool {
		return ! $this->order->getItems();
	}

	/**
	 * {@inheritdoc}
	 */
	public function summary(): Summary {
		return new Summary( $this->order );
	}

	public function setNumber(): void {
		$now = new \DateTime();

		$lastOrder = $this->em->getRepository(Order::class)->lastByNumber();
		if(!$lastOrder) {
			$number = '01';
		} else {
			$pos = strpos($lastOrder->getNumber(), $now->format('y'), 0);
			if($pos !== false && $pos === 0) {
				$number = preg_replace('/^'.$now->format('y').'/', '', $lastOrder->getNumber());
				$number = (int) $number + 1;
				if($number < 10) {
					$number = '0'.$number;
				}else {
					$number = (string) $number;
				}
			} else {
				$number = '01';
			}
		}

		$this->order->setNumber($now->format('y').$number);
	}

	public function addState($state, $user = null): void
	{
		$state = $this->em->getRepository(State::class)->findOneByName('state.'.$state);
		if($state) {
			$orderState = new OrderState();
			$orderState->setOrder($this->order);
			$orderState->setState($state);

			if($user) {
				$orderState->setUser($user);
			}
			$this->em->persist($orderState);
			$this->order->addState($orderState);

			if($state->isPaid() && !$this->order->getPayIt()) {
				$this->order->setPayIt(new \DateTime());
			}

			$this->em->flush();
		}
	}

	public function setDataBillingByUser($user = null, $order = null)
	{
		if(!$user) {
			$user = $this->order->getUser();
		}

		if(!$order) {
			$order = $this->order;
		}

		$billing = new Billing();
		$billing->setCompany($user->getCompany());
		$billing->setIntraVAT($user->getIntraVAT());
		$billing->setFirstname($user->getFirstname());
		$billing->setLastname($user->getLastname());
		$billing->setAddress($user->getAddress());
		$billing->setComplementAddress($user->getComplementAddress());
		$billing->setPostalCode($user->getPostalCode());
		$billing->setCity($user->getCity());
		$billing->setCountry($user->getCountry());
		$billing->setPhone($user->getPhone());

		$order->setBilling($billing);

	}

	public function setDataShipmentByUser($user = null, $order = null)
	{
		if(!$user) {
			$user = $this->order->getUser();
		}

		if(!$order) {
			$order = $this->order;
		}

		$shipment = new Shipment();
		$shipment->setCompany($user->getCompany());
		$shipment->setIntraVAT($user->getIntraVAT());
		$shipment->setFirstname($user->getFirstname());
		$shipment->setLastname($user->getLastname());
		$shipment->setAddress($user->getAddress());
		$shipment->setComplementAddress($user->getComplementAddress());
		$shipment->setPostalCode($user->getPostalCode());
		$shipment->setCity($user->getCity());
		$shipment->setCountry($user->getCountry());
		$shipment->setPhone($user->getPhone());

		$order->setShipment($shipment);

	}
}
