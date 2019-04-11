<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-15
 */

namespace Octopouce\ShopBundle\Form\Plugin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BankWireType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

	}

	public function getName()
	{
		return 'bank_wire';
	}
}
