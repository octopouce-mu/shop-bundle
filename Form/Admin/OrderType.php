<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form\Admin;

use Doctrine\ORM\EntityRepository;
use Octopouce\ShopBundle\Entity\Discount;
use Octopouce\ShopBundle\Entity\Order;
use Octopouce\ShopBundle\Entity\State;
use Octopouce\ShopBundle\Form\BillingType;
use Octopouce\ShopBundle\Form\ShipmentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderType extends AbstractType
{
	private $translator;

	/**
	 * OrderStateType constructor.
	 *
	 * @param $translator
	 */
	public function __construct(TranslatorInterface $translator ) {
		$this->translator = $translator;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
    {

	    $translator = $this->translator;

        $builder
	        ->add('billing', BillingType::class, [
		        'user' => $options['user'],
	        ])
	        ->add('shipment', ShipmentType::class, [
		        'user' => $options['user'],
		        'required' => false
	        ])

	        ->add('state', EntityType::class, [
		        'class' => State::class,
		        'choice_label' => function ($state) use ($translator) {
			        return $translator->trans($state->getName());
		        },
		        'placeholder' => 'Choisir un état',
		        'mapped' => false
	        ])
	        ->add('discount', EntityType::class, [
	        	'class' => Discount::class,
		        'required' => false
	        ])
	        ->add('cart', EntityType::class, [
	        	'class' => Order::class,
		        'query_builder' => function (EntityRepository $er) use ($builder) {
			        return $er->createQueryBuilder('o')
				        ->andWhere('o.paymentInstruction IS NULL')
				        ->andWhere('o.number IS NULL')
				        ->andWhere('o.user = :user')
				        ->setParameter('user', $builder->getData()->getUser())
		                ->orderBy('o.id', 'ASC');
		        },
		        'mapped' => false,
		        'required' => false,
		        'choice_label' => 'number',
		        'placeholder' => 'Sélectionner un panier'
	        ])
	        ->add('order', EntityType::class, [
		        'class' => Order::class,
		        'query_builder' => function (EntityRepository $er) use ($builder) {
			        return $er->createQueryBuilder('o')
			                  ->andWhere('o.number IS NOT NULL')
						        ->andWhere('o.user = :user')
						        ->setParameter('user', $builder->getData()->getUser())
			                  ->orderBy('o.id', 'ASC');
		        },
		        'choice_label' => 'number',
		        'mapped' => false,
		        'required' => false,
		        'placeholder' => 'Sélectionner une commande'
	        ])

	        ->add('submit', SubmitType::class, [
	        	'label' => 'Créer et ajouter les articles'
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
