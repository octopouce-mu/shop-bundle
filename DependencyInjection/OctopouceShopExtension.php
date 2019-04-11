<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 30/05/2018
 */

namespace Octopouce\ShopBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OctopouceShopExtension extends Extension implements PrependExtensionInterface
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yaml');
	}

	public function prepend(ContainerBuilder $container)
	{
		$newConfigVich = [
//			'vich_uploader' => [
				'mappings' => [
					'shop_image' => [
						'upload_destination' => '%kernel.project_dir%/public/uploads/shop',
						'directory_namer' => [
							'service' => 'Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer',
							'options' => [
								'date_time_format' => 'Y/m'
							]
						]
					]
				]
//			]
		];

		$container->prependExtensionConfig('vich_uploader', $newConfigVich);

	}
}
