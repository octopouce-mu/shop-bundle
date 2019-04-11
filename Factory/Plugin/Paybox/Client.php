<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox;

use Octopouce\ShopBundle\Factory\Plugin\Paybox\Client\AbstractHttp;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Client\GuzzleHttp;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum\Activity;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum\Currency;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Enum\Version;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Exception\InvalidRequestPropertiesException;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Request\InquiryRequest;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Request\RequestInterface;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\DirectPlusResponse;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\DirectResponse;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\InquiryResponse;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Client
{
	const API_URL_PRODUCTION = 'https://ppps.paybox.com/PPPS.php';

	const API_URL_RESCUE = 'https://ppps1.paybox.com/PPPS.php';

	const API_URL_TEST = 'https://preprod-ppps.paybox.com/PPPS.php';

	/**
	 * @var ValidatorInterface
	 */
	private $validator;

	/**
	 * @var AbstractHttp
	 */
	private $httpClient;

	/**
	 * @var array
	 */
	private $options;


	public function __construct(array $options = [], AbstractHttp $httpClient = null)
	{
		$resolver = new OptionsResolver();
		$this->configureOptions($resolver);
		$this->options = $resolver->resolve($options);
		AnnotationRegistry::registerLoader('class_exists');
		$this->validator = Validation::createValidatorBuilder()
		                             ->enableAnnotationMapping()
		                             ->getValidator();
		$this->httpClient = $httpClient ? $httpClient : new GuzzleHttp();
		$this->httpClient->setOptions($this->options);
		$this->httpClient->init();
	}

	/**
	 * @param RequestInterface $request
	 *
	 * @return DirectResponse
	 */
	public function sendDirectRequest(RequestInterface $request)
	{
		if ($request->getRequestType() >= RequestInterface::SUBSCRIBER_AUTHORIZE) {
			throw new \InvalidArgumentException(
				'Direct Plus requests must be passed onto '.__CLASS__.'::sendDirectPlusRequest method.'
			);
		}
		if ($request instanceof InquiryRequest) {
			throw new \InvalidArgumentException(
				'Inquiry requests must be passed onto '.__CLASS__.'::sendInquiryRequest method.'
			);
		}
		return $this->request($request);
	}

	/**
	 * @param RequestInterface $request
	 *
	 * @return DirectPlusResponse
	 */
	public function sendDirectPlusRequest(RequestInterface $request)
	{
		if ($request->getRequestType() < RequestInterface::SUBSCRIBER_AUTHORIZE) {
			throw new \InvalidArgumentException(
				'Direct requests must be passed onto '.__CLASS__.'::sendDirectRequest method.'
			);
		}
		return $this->request($request, DirectPlusResponse::class);
	}

	/**
	 * @param InquiryRequest $request
	 *
	 * @return InquiryResponse
	 */
	public function sendInquiryRequest(InquiryRequest $request)
	{
		return $this->request($request, InquiryResponse::class);
	}

	/**
	 * @param RequestInterface $request
	 * @param string           $responseClass
	 *
	 * @return ResponseInterface
	 *
	 * @throws Exception\InvalidRequestPropertiesException
	 * @throws Exception\PayboxException
	 */
	private function request(RequestInterface $request, $responseClass = DirectResponse::class)
	{
		$errors = $this->validator->validate($request);
		if ($errors->count() > 0) {
			throw new InvalidRequestPropertiesException($request, $errors);
		}
		return $this->httpClient->call($request->getRequestType(), $request->getParameters(), $responseClass);
	}

	/**
	 * Paybox base options validation.
	 *
	 * @param OptionsResolver $resolver
	 */
	private function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'timeout' => 10,
			'production' => false,
			'paybox_default_currency' => Currency::EURO,
		]);
		$resolver->setDefined([
			'paybox_default_activity',
		]);
		$resolver->setRequired([
			'paybox_version', // Paybox Direct Plus protocol
			'paybox_site',
			'paybox_rank',
			'paybox_identifier',
			'paybox_key',
		]);
		$resolver->setAllowedTypes('timeout', 'int');
		$resolver->setAllowedTypes('production', 'bool');
		$resolver->setAllowedTypes('paybox_version', 'string');
		$resolver->setAllowedTypes('paybox_default_currency', 'int');
		$resolver->setAllowedTypes('paybox_site', 'string');
		$resolver->setAllowedTypes('paybox_rank', 'string');
		$resolver->setAllowedTypes('paybox_identifier', 'string');
		$resolver->setAllowedTypes('paybox_key', 'string');
		$resolver->setAllowedValues('paybox_version', Version::getConstants());
		$resolver->setAllowedValues('paybox_default_activity', Activity::getConstants());
	}
}
