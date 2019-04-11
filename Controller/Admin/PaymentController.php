<?php

namespace Octopouce\ShopBundle\Controller\Admin;

use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payments")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/{id}/update", name="octopouce_shop_admin_payment_update")
     */
    public function updateFinancialTransaction(FinancialTransaction $financialTransaction)
    {
        return $this->render('@OctopouceShop/Admin/payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
