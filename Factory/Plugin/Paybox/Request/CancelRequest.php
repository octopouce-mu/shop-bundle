<?php

/*
 * This file is part of the Nexylan packages.
 *
 * (c) Nexylan SAS <contact@nexylan.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Request;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class CancelRequest extends AbstractNumberedReferencedTransactionRequest
{
    /**
     * {@inheritdoc}
     */
    public function getRequestType()
    {
        return RequestInterface::CANCEL;
    }
}
