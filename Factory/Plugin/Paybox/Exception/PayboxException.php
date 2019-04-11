<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Exception;


use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\ResponseInterface;

class PayboxException extends \RuntimeException
{
	/**
	 * @var ResponseInterface
	 */
	private $response;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(ResponseInterface $response, \Exception $previous = null)
	{
		parent::__construct('', $response->getCode(), $previous);
		$this->message = sprintf('%05d: %s', $response->getCode(), $response->getComment());
		$this->response = $response;
	}

	/**
	 * @return ResponseInterface
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
