<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum;

use Greg0ire\Enum\AbstractEnum;

final class Status extends AbstractEnum
{
	const REFUNDED = 'Remboursé';
	const CANCELED = 'Annulé';
	const AUTHORIZED = 'Autorisé';
	const CAPTURED = 'Capturé';
	const CREDIT = 'Crédit';
	const REFUSED = 'Refusé';
	const BALANCE_INQUIRY = 'Demande de solde (Carte cadeaux)';
	const SUPPORT_REJECTION = 'Rejet support';
}
