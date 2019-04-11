<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Event;


use App\Entity\Shopping\Order;
use App\Entity\Shopping\OrderItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderRecalculateSubscriber implements EventSubscriberInterface {

	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct( SessionInterface $session ) {
		$this->session = $session;
	}

	public static function getSubscribedEvents(): array {
		return [
			OrderEvents::ORDER_UPDATED => 'recalculate',
		];
	}

	public function recalculate( GenericEvent $event ): void {

		/** @var Order $entity */
		$entity          = $event->getSubject();

		$itemsTotal      = 0;
		$itemsPriceTotal = 0;

		if($entity->getItems()) {
			/** @var OrderItem $item */
			foreach ( $entity->getItems() as $item ) {
				$itemsTotal += $item->getQuantity();
				$item->setPriceTotal( $item->getPrice() * $item->getQuantity() );
				$itemsPriceTotal += $item->getPriceTotal();
			}
		}


		if ( $entity->getDiscount() ) {
			$itemsPriceTotal -= $itemsPriceTotal * $entity->getDiscount()->getDiscount() / 100;
		}

		$priceTotal = $itemsPriceTotal;

		$entity->setItemsTotal( $itemsTotal );
		$entity->setItemsPriceTotal( $itemsPriceTotal );
		$entity->setPriceTotal( $priceTotal );

	}
}
