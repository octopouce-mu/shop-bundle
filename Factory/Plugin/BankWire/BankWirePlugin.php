<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-15
 */

namespace Octopouce\ShopBundle\Factory\Plugin\BankWire;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

class BankWirePlugin extends AbstractPlugin
{
	public function checkPaymentInstruction(PaymentInstructionInterface $instruction)
	{
		$errorBuilder = new ErrorBuilder();
		$data = $instruction->getExtendedData();

		if ($errorBuilder->hasErrors()) {
			throw $errorBuilder->getException();
		}
	}

	public function processes($name)
	{
		return 'bank_wire' === $name;
	}

	public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
	{
		$data = $transaction->getExtendedData();

		$transaction->setResponseCode(PluginInterface::RESPONSE_CODE_PENDING);
		$transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
		throw new PaymentPendingException('Payment is pending.');
	}

	public function approve(FinancialTransactionInterface $transaction, $retry)
	{
		$data = $transaction->getExtendedData();
		$transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
		$transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
		$transaction->setProcessedAmount($transaction->getRequestedAmount());
	}
}
