<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum;

use Greg0ire\Enum\AbstractEnum;

final class Version extends AbstractEnum
{
	const DIRECT = '00103';
	const DIRECT_PLUS = '00104';
}
