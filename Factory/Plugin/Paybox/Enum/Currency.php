<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum;

use Greg0ire\Enum\AbstractEnum;

final class Currency extends AbstractEnum
{
	const EURO = 978;
	const US_DOLLAR = 840;
	const CFA = 952;
}
