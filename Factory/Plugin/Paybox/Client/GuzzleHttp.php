<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Client;

use GuzzleHttp\Client;

final class GuzzleHttp extends AbstractHttp
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		$this->client = new Client([
			'base_uri' => $this->baseUrl,
			'timeout' => $this->timeout,
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function request($parameters)
	{
		$response = $this->client->post('', [
			'form_params' => $parameters,
		]);
		return (string) $response->getBody();
	}
}
