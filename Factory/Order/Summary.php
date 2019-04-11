<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order;


use Octopouce\ShopBundle\Entity\Order;

final class Summary {
	/**
	 * @var Order
	 */
	private $order;

	/**
	 * Summary constructor.
	 *
	 * @param Order $order
	 */
	public function __construct( Order $order ) {
		$this->order = $order;
	}

	/**
	 * Return
	 *
	 * @return float
	 */
	public function getItemsPriceTotal() {
		return $this->order->getItemsPriceTotal();
	}

	/**
	 * Return
	 *
	 * @return float
	 */
	public function getPriceTotal() {
		return $this->order->getPriceTotal();
	}

	/**
	 * Return discount price
	 *
	 * @return float
	 */
	public function getDiscount() {
		$discount = 0;
		if ( $this->order->getDiscount() ) {
			$discount = $this->getPriceItemsBeforeDiscount() * $this->order->getDiscount()->getDiscount() / 100;
		}

		return $discount;
	}

	/**
	 * @return float
	 */
	public function getPriceItemsBeforeDiscount() {
		$price = 0;
		foreach ( $this->order->getItems() as $item ) {
			$price += $item->getPriceTotal();
		}

		return $price;
	}
}
