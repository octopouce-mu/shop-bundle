<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Exception;


use Octopouce\ShopBundle\Factory\Plugin\Paybox\Request\RequestInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidRequestPropertiesException extends \LogicException
{
	/**
	 * @var ConstraintViolationListInterface
	 */
	private $errors;

	/**
	 * InvalidRequestPropertiesException constructor.
	 *
	 * @param RequestInterface                $request
	 * @param ConstraintViolationListInterface $errors
	 * @param \Exception                       $previous
	 */
	public function __construct(RequestInterface $request, ConstraintViolationListInterface $errors, \Exception $previous = null)
	{
		parent::__construct('', 0, $previous);
		$this->message = PHP_EOL.(string) $errors;
		$this->errors = $errors;
	}

	/**
	 * @return ConstraintViolationListInterface
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
