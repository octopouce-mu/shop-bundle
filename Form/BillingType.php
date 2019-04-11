<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form;

use Octopouce\ShopBundle\Entity\Billing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    /** @var ?User $user */
	    $user = $options['user'];

	    if($user && $user->getType() === 'company') {
		    $builder
			    ->add('company', TextType::class, [
				    'label' => 'company',
			    ])
			    ->add('intraVAT', TextType::class, [
				    'label' => 'TVA Intra',
			    ])
		    ;
	    }

	    $builder
		    ->add('firstname', TextType::class, [
			    'label' => 'firstname',
		    ])
		    ->add('lastname', TextType::class, [
			    'label' => 'lastname',
		    ])
		    ->add('address', TextType::class, [
			    'label' => 'address',
		    ])
		    ->add('complementAddress', TextType::class, [
			    'label' => 'complement_address',
			    'required' => false,
		    ])
		    ->add('postalCode', TextType::class, [
			    'label' => 'postal_code',
		    ])
		    ->add('city', TextType::class, [
			    'label' => 'city',
		    ])
		    ->add('country', CountryType::class, [
			    'label' => 'country',
			    'attr' => ['class' => 'custom-select'],
			    'preferred_choices' => ['FR'],
		    ])
		    ->add('phone', TextType::class, [
			    'label' => 'phone',
			    'help' => 'Indicatif + Numéro (Ex: +33612345678)',
		    ])
	    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Billing::class,
	        'user' => null
        ]);
    }
}
