<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form\Admin;

use App\Entity\Account\User;
use Doctrine\ORM\EntityRepository;
use Octopouce\ShopBundle\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderUserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
	        ->add('user', EntityType::class, [
	        	'class' => User::class,
		        'choice_label' => function ($user) {
			        return $user->getFirstname(). ' ' .$user->getLastname().' ('.$user->getEmail().')';
		        },
		        'query_builder' => function (EntityRepository $er) {
			        return $er->createQueryBuilder('u')
			                  ->orderBy('u.firstname', 'ASC');
		        },
		        'placeholder' => 'Choisissez un utilisateur'
	        ])
	        ->add('submit', SubmitType::class, [
	        	'label' => 'submit'
	        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
