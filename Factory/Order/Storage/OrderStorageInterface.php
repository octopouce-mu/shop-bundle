<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-11
 */

namespace Octopouce\ShopBundle\Factory\Order\Storage;


use Octopouce\ShopBundle\Entity\Order;

interface OrderStorageInterface
{
	public function set(string $orderId): void ;
	public function get(): string ;
	public function has(): bool ;
	public function remove(): void ;
	public function getOrderById(): ?Order ;

}
