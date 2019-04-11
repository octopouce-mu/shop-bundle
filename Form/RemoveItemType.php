<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form;

use Octopouce\ShopBundle\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RemoveItemType extends AbstractType
{
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    $builder->setAction($this->urlGenerator->generate('cart_remove_item', ['id' => $builder->getData()->getId()]));

        $builder
	        ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class
        ]);
    }
}
