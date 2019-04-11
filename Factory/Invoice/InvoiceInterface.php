<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-26
 */

namespace Octopouce\ShopBundle\Factory\Invoice;

use Octopouce\ShopBundle\Entity\Billing;
use Octopouce\ShopBundle\Entity\Invoice;
use Octopouce\ShopBundle\Entity\Order;

interface InvoiceInterface
{
	public function getInvoice(): Invoice;

	public function setInvoice( Invoice $invoice ): void;

	public function save(): void;

	public function generate();

	public function saveAndGenerate();

	public function getFilename(): string;

	public function create(Order $order): void;

	public function getAddress(): string;

	public function getAddressClient(Billing $billing): string;

	public function isChanged(): bool;

	public function render();

	public function getNumber(): string;

	public function send(): void;

	public function createAndSend(Order $order): void;
}
