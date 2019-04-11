<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form;

use Octopouce\ShopBundle\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$builder->add('billing', BillingType::class, [
			'user' => $options['user']
		]);

		$digital = true;
		foreach ($builder->getData()->getItems() as $item) {
			if(!$item->getDigital()){
				$digital = false;
				break;
			}
		}

		if(!$digital) {
			$builder->add('shipment', ShipmentType::class, [
				'user' => $options['user']
			]);
		}

		$builder->add('submit', SubmitType::class, [
				'label' => 'submit',
				'attr' => [
					'class' => 'btn-primary btn-icon icon-ico-right'
				]
			])
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Order::class,
			'user' => null
		]);
	}
}
