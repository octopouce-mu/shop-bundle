<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum;

use Greg0ire\Enum\AbstractEnum;

final class Activity extends AbstractEnum
{
	const NOT_SPECIFIED = 20;
	const PHONE_REQUEST = 21;
	const MAIL_REQUEST = 22;
	const MINITEL_REQUEST = 23;
	const WEB_REQUEST = 24;
	const RECURRING_PAYMENT = 27;
}
