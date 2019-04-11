<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-15
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox;


use Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum\Currency;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum\Version;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Exception\PayboxException;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Request\AuthorizeAndCaptureRequest;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\DirectResponse;
use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

class PayboxPlugin extends AbstractPlugin
{

	private $payboxSite;
	private $payboxRank;
	private $payboxIdentifier;
	private $payboxKey;
	private $payboxProduction;

	public function __construct( $payboxSite = '', $payboxRank = '', $payboxIdentifier = '', $payboxKey = '', $payboxProduction = false ) {
		parent::__construct();
		$this->payboxSite        = $payboxSite;
		$this->payboxRank        = $payboxRank;
		$this->payboxIdentifier  = $payboxIdentifier;
		$this->payboxKey         = $payboxKey;
		$this->payboxProduction  = $payboxProduction;
	}


	public function processes($name)
	{
		return 'paybox' === $name;
	}

	public function checkPaymentInstruction(PaymentInstructionInterface $instruction)
	{
		$errorBuilder = new ErrorBuilder();
		$data = $instruction->getExtendedData();

		if (!$data->get('name')) {
			$errorBuilder->addDataError('name', 'form.error.required');
		}

		if (!$data->get('number')) {
			$errorBuilder->addDataError('number', 'form.error.required');
		}

		if (!$data->get('expires')) {
			$errorBuilder->addDataError('expires', 'form.error.required');
		}

		if (!$data->get('cvv')) {
			$errorBuilder->addDataError('cvv', 'form.error.required');
		}

//		if ($instruction->getAmount() > 10000) {
//			$errorBuilder->addGlobalError('form.error.credit_card_max_limit_exceeded');
//		}

		// more checks here ...

		if ($errorBuilder->hasErrors()) {
			throw $errorBuilder->getException();
		}
	}

	public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
	{
		$data = $transaction->getExtendedData();

		$paybox = new Client([
			// Optional parameters:
			'timeout' => 30,        // Change the request timeout.
			'production' => $this->payboxProduction,   // Set to true to use the production API URL.
			// Required parameters:
			'paybox_version' => Version::DIRECT,
			'paybox_site' => $this->payboxSite,
			'paybox_rank' => $this->payboxRank,
			'paybox_identifier' => $this->payboxIdentifier,
			'paybox_key' => $this->payboxKey,
			'paybox_default_currency' => Currency::EURO
		]);

		$amount = $transaction->getRequestedAmount() * 100;

		$request = new AuthorizeAndCaptureRequest('CMD-'.$this->getOrder($data), (int) $amount, $this->getNumber($data), $this->getDate($data));
		$request->setCardVerificationValue($this->getCVV($data));

		try {
			/** @var DirectResponse $response */
			$response = $paybox->sendDirectRequest($request);
		} catch (PayboxException $e) {
			$ex = new FinancialException($e->getMessage());
			$ex->setFinancialTransaction($transaction);
			$transaction->setResponseCode($e->getCode());
			$transaction->setReasonCode($e->getMessage());

			throw $ex;
		}

		switch ($response->getCode()) {
			case 0:
				break;
			default:
				$ex = new FinancialException('PaymentStatus is not completed: '.$response->getComment());
				$ex->setFinancialTransaction($transaction);
				$transaction->setResponseCode('Failed');
				$transaction->setReasonCode($response->getCode());
				throw $ex;
		}

		$transaction->setReferenceNumber($response->getTransactionNumber());
		$transaction->setProcessedAmount($transaction->getRequestedAmount());
		$transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
		$transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
	}

	protected function getName(ExtendedDataInterface $data)
	{
		if ($data->has('name')) {
			return $data->get('name');
		}

		throw new \RuntimeException('You must configure a return name.');
	}

	protected function getDate(ExtendedDataInterface $data)
	{
		if ($data->has('expires')) {
			return str_replace([' ', '/'], '', $data->get('expires'));;
		}

		throw new \RuntimeException('You must configure a return date.');
	}

	protected function getNumber(ExtendedDataInterface $data)
	{
		if ($data->has('number')) {
			return str_replace(' ', '', $data->get('number'));
		}

		throw new \RuntimeException('You must configure a return number.');
	}

	protected function getCVV(ExtendedDataInterface $data)
	{
		if ($data->has('cvv')) {
			return (string) $data->get('cvv');
		}

		throw new \RuntimeException('You must configure a return cvv.');
	}

	protected function getOrder(ExtendedDataInterface $data)
	{
		if ($data->has('order_number')) {
			return $data->get('order_number');
		}

		throw new \RuntimeException('You must configure a return order.');
	}
}
