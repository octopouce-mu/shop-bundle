<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-26
 */

namespace Octopouce\ShopBundle\Factory\Invoice;

use Doctrine\ORM\EntityManagerInterface;
use Octopouce\AdminBundle\Service\Transformer\OptionTransformer;
use Octopouce\AdminBundle\Utils\MailerService;
use Octopouce\ShopBundle\Entity\Billing;
use Octopouce\ShopBundle\Entity\Invoice;
use Octopouce\ShopBundle\Entity\Order;
use Symfony\Component\Intl\Intl;

class InvoiceFactory implements InvoiceInterface
{
	private $generator;

	private $templating;

	private $em;

	private $options;

	private $invoice;

	private $dir;

	private $mailer;


	public function __construct( $generator, \Twig_Environment $templating, EntityManagerInterface $em, OptionTransformer $optionTransformer, $dir, MailerService $mailer ) {
		$this->generator = $generator;
		$this->templating = $templating;
		$this->em = $em;
		$this->options = $optionTransformer->getOptionsWithKeyName();
		$this->invoice = new Invoice();
		$this->dir = $dir;
		$this->mailer = $mailer;
	}

	public function getInvoice(): Invoice
	{
		return $this->invoice;
	}

	public function setInvoice( Invoice $invoice ): void
	{
		$this->invoice = $invoice;
	}


	public function save(): void
	{
		if($this->generator) {
			$this->generator->generateFromHtml($this->render(), $this->dir.$this->getFilename());
		}
	}

	public function generate()
	{
		if($this->generator) {
			return $this->generator->getOutputFromHtml($this->render());
		}

		return null;
	}

	public function saveAndGenerate()
	{
		$this->save();
		return $this->generate();
	}


	public function getFilename(): string
	{
		$name =  preg_replace('/[^a-z0-9_-]/i', '', 'facture_'. $this->invoice->getNumber());
		return $name.'.pdf';
	}

	public function createAndSend(Order $order): void
	{
		$this->create($order);
		$this->send();
	}

	public function send(): void
	{
		if($this->generator) {
			$file = $this->dir.$this->getFilename();

			if(!file_exists($file)) {
				$this->save();
			}

			$mail = $this->templating->render('cart/mails/invoice.html.twig', array(
				'invoice'  => $this->invoice,
				'user' => $this->invoice->getOrder()->getUser()
			));

			$this->mailer->send(
				$this->invoice->getOrder()->getUser()->getEmail(),
				'Reçu pour votre paiement de la commande n°'.$this->invoice->getOrder()->getNumber(),
				$mail,
				$file
			);

			$this->invoice->setSendAt(new \DateTime());
		}
	}

	public function create(Order $order): void
	{
		$this->invoice->setOrder($order);
		$this->invoice->setItemsPriceTotal($order->getItemsPriceTotal());
		$this->invoice->setItemsTotal($order->getItemsTotal());
		$this->invoice->setPriceTotal($order->getPriceTotal());
		$this->invoice->setNumber($this->getNumber());
		$this->invoice->setAddress($this->getAddress());
		$this->invoice->setAddressClient($this->getAddressClient($order->getBilling()));
		$this->invoice->setPayIt($order->getPayIt());

		$items = [];
		foreach ($order->getItems() as $item) {
			$items[] = [
				'name' => $item->getName(),
				'quantity' => $item->getQuantity(),
				'price' => $item->getPrice(),
				'total' => $item->getPriceTotal()
			];
		}
		$items = json_encode($items);
		$this->invoice->setItems($items);

		$this->em->persist($this->invoice);

		$order->addInvoice($this->invoice);

		$this->em->flush();
	}

	public function getAddress(): string
	{
		$address = '';
		$address .= $this->options['COMPANY_NAME']->getValue().'<br>';
		$address .= $this->options['COMPANY_ADDRESS']->getValue().'<br>';
		if(isset($this->options['COMPANY_ADDRESS2']) && $this->options['COMPANY_ADDRESS2']->getValue()) {
			$address .= $this->options['COMPANY_ADDRESS2']->getValue().'<br>';
		}
		$address .= $this->options['COMPANY_POSTAL_CODE']->getValue(). ' ' . $this->options['COMPANY_CITY']->getValue().'<br>';
		$address .= 'France';

		return $address;
	}

	public function getAddressClient(Billing $billing): string
	{
		$address = '';
		if($billing->getCompany()) {
			$address .= $billing->getCompany().'<br>';
		}
		$address .= $billing->getFirstname(). ' ' . $billing->getLastname().'<br>';
		$address .= $billing->getAddress().'<br>';
		if($billing->getComplementAddress()) {
			$address .= $billing->getComplementAddress().'<br>';
		}
		$address .= $billing->getPostalCode(). ' ' . $billing->getCity().'<br>';
		$address .= Intl::getRegionBundle()->getCountryName($billing->getCountry());

		return $address;
	}

	public function isChanged(): bool
	{
		$order = $this->invoice->getOrder();
		$changed = false;

		if($order) {
			if($this->invoice->getPriceTotal() !== $order->getPriceTotal()) {
				$changed = true;
			} elseif($this->invoice->getItemsTotal() !== $order->getItemsTotal()) {
				$changed = true;
			} elseif($this->invoice->getItemsPriceTotal() !== $order->getItemsPriceTotal()) {
				$changed = true;
			}
		}

		return $changed;
	}

	public function render()
	{
		return $this->templating->render('@OctopouceShop/invoice/pdf.html.twig', array(
			'invoice'  => $this->invoice
		));
	}

	public function getNumber(): string
	{
		$now = new \DateTime();

		$lastInvoice = $this->em->getRepository(Invoice::class)->lastByNumber();
		if(!$lastInvoice) {
			$number = '01';
		} else {
			$pos = strpos($lastInvoice->getNumber(), $now->format('y'), 0);
			if($pos !== false && $pos === 0) {
				$number = preg_replace('/^'.$now->format('y').'/', '', $lastInvoice->getNumber());
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


		return $now->format('y').$number;
	}
}
