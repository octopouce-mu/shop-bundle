<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Event;


final class OrderEvents
{
	/**
	 * @Event("Symfony\Component\EventDispatcher\GenericEvent")
	 * @var string
	 */
	public const ORDER_CREATED = 'order.created';

	/**
	 * @Event("Symfony\Component\EventDispatcher\GenericEvent")
	 * @var string
	 */
	public const ORDER_UPDATED = 'order.updated';
}
