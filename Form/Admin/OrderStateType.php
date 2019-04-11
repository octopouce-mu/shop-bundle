<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 21/03/2018
 */

namespace Octopouce\ShopBundle\Form\Admin;

use Doctrine\ORM\EntityRepository;
use Octopouce\ShopBundle\Entity\OrderState;
use Octopouce\ShopBundle\Entity\State;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderStateType extends AbstractType
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
	        ->add('state', EntityType::class, [
	        	'class' => State::class,
		        'choice_label' => function ($state) use ($translator) {
			        return $translator->trans($state->getName());
		        },
		        'required' => true,
		        'query_builder' => function (EntityRepository $er) use ($options) {
			        return $er->createQueryBuilder('os')
			                  ->andWhere('os.name != :name')
				              ->setParameter('name', $options['last_state'])
			                  ->orderBy('os.name', 'ASC');
		        },
		        'placeholder' => 'Choisir un état',
		        'translation_domain' => 'messages'
	        ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderState::class,
	        'last_state' => null
        ]);
    }
}
