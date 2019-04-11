<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-15
 */

namespace Octopouce\ShopBundle\Form\Plugin;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class PayboxType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$builder
			->add('name', TextType::class, [
				'required' => false,
				'label' => 'form.label.name'
			])
			->add('number', TelType::class, [
				'constraints' => array(
					new Length(['min' => 16, 'max' => 19])
				),
				'attr' => [
					'maxlength' => 19,
				],
				'required' => false,
				'label' => 'form.label.number'
			])

			->add('expires', TelType::class, [
				'attr' => [
					'maxlength' => 7,
				],
				'required' => false,
				'label' => 'form.label.year'
			])
			->add('cvv', TelType::class, [
				'attr' => [
					'maxlength' => 4,
				],
				'constraints' => array(
					new Length(['min' => 3, 'max' => 3])
				),
				'required' => false,
				'label' => 'form.label.cvv'
			])
		;
	}

	public function getName()
	{
		return 'paybox';
	}
}
