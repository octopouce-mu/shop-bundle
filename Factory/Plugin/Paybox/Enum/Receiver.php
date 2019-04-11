<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum;

use Greg0ire\Enum\AbstractEnum;

final class Receiver extends AbstractEnum
{
	const PAYPAL = 'PAYPAL';
	const EMS = 'EMS';
	const ATOSBE = 'ATOSBE';
	const BCMC = 'BCMC';
	const PSC = 'PSC';
	const FINAREF = 'FINAREF';
	const BUYSTER = 'BUYSTER';
	const ONEY = '34ONEY';
}
