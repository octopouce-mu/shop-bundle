<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderIdSaveInSessionSubscriber implements EventSubscriberInterface
{
	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct(SessionInterface $session)
	{
		$this->session = $session;
	}
	public static function getSubscribedEvents(): array
	{
		return [
			OrderEvents::ORDER_CREATED => 'onOrderCreated',
		];
	}
	public function onOrderCreated(GenericEvent $event): void
	{
		$this->session->set('orderId', $event->getSubject()->getId());
	}
}
