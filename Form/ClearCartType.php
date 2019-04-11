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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClearCartType extends AbstractType
{
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    $builder->setAction($this->urlGenerator->generate('cart_clear'));

        $builder
	        ->add('submit', SubmitType::class, [
	        	'label' => 'Vider le panier',
		        'attr' => ['class' => 'btn-primary btn-small']
	        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class
        ]);
    }
}
