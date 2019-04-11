<?php

namespace Octopouce\ShopBundle\Form\Admin;

use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinancialTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('requestedAmount')
	        ->add('responseCode')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => null
        ]);
    }
}
