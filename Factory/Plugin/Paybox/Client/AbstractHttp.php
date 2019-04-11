<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-27
 */

namespace Octopouce\ShopBundle\Factory\Plugin\Paybox\Client;


use Octopouce\ShopBundle\Factory\Plugin\Paybox\Client;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Exception\PayboxException;
use Octopouce\ShopBundle\Factory\Plugin\Paybox\Response\ResponseInterface;


abstract class AbstractHttp
{
	/**
	 * @var int
	 */
	protected $timeout;

	/**
	 * @var int
	 */
	protected $baseUrl = Client::API_URL_TEST;

	/**
	 * @var string[]
	 */
	private $baseParameters;

	/**
	 * @var int
	 */
	private $defaultCurrency;

	/**
	 * @var int|null
	 */
	private $defaultActivity = null;

	/**
	 * @var int
	 */
	private $questionNumber;

	/**
	 * Constructor.
	 */
	final public function __construct()
	{
		try {
			$this->questionNumber = random_int(0, time());
		} catch (\Exception $exception) {
			$this->questionNumber = rand(0, time());
		}
	}

	/**
	 * @param array $options
	 */
	final public function setOptions($options)
	{
		$this->timeout = $options['timeout'];
		$this->baseUrl = true === $options['production'] ? Client::API_URL_PRODUCTION : Client::API_URL_TEST;
		$this->baseParameters = [
			'VERSION' => $options['paybox_version'],
			'SITE' => $options['paybox_site'],
			'RANG' => $options['paybox_rank'],
			'IDENTIFIANT' => $options['paybox_identifier'],
			'CLE' => $options['paybox_key'],
		];
		$this->defaultCurrency = $options['paybox_default_currency'];
		if (array_key_exists('paybox_default_activity', $options)) {
			$this->defaultActivity = $options['paybox_default_activity'];
		}
	}

	/**
	 * Calls PayBox Direct platform with given operation type and parameters.
	 *
	 * @param int      $type          Request type
	 * @param string[] $parameters    Request parameters
	 * @param string   $responseClass
	 *
	 * @return ResponseInterface The response content
	 *
	 * @throws PayboxException
	 */
	final public function call($type, array $parameters, $responseClass)
	{
		if (!in_array(ResponseInterface::class, class_implements($responseClass))) {
			throw new \InvalidArgumentException('The response class must implement '.ResponseInterface::class.'.');
		}
		$bodyParams = array_merge($parameters, $this->baseParameters);
		$bodyParams['TYPE'] = $type;
		$bodyParams['NUMQUESTION'] = $this->questionNumber;
		$bodyParams['DATEQ'] = null !== $parameters['DATEQ'] ? $parameters['DATEQ'] : date('dmYHis');
		// Restore default_currency from parameters if given
		if (array_key_exists('DEVISE', $parameters)) {
			$bodyParams['DEVISE'] = null !== $parameters['DEVISE'] ? $parameters['DEVISE'] : $this->defaultCurrency;
		}
		if (!array_key_exists('ACTIVITE', $parameters) && $this->defaultActivity) {
			$bodyParams['ACTIVITE'] = $this->defaultActivity;
		}
		// `ACTIVITE` must be a string of 3 numbers to get it working with Paybox API.
		if (array_key_exists('ACTIVITE', $bodyParams)) {
			$bodyParams['ACTIVITE'] = str_pad($bodyParams['ACTIVITE'], 3, '0', STR_PAD_LEFT);
		}
		$response = $this->request($bodyParams);
		// Generate results array
		$results = [];
		foreach (explode('&', $response) as $element) {
			list($key, $value) = explode('=', $element);
			$value = utf8_encode(trim($value));
			$results[$key] = $value;
		}
		$this->questionNumber = (int) $results['NUMQUESTION'] + 1;
		/** @var ResponseInterface $response */
		$response = new $responseClass($results);
		if (!$response->isSuccessful()) {
			throw new PayboxException($response);
		}
		return $response;
	}

	/**
	 * Init and setup http client with PayboxDirectPlus SDK options.
	 */
	abstract public function init();

	/**
	 * Sends a request to the server, receive a response and returns it as a string.
	 *
	 * @param string[] $parameters Request parameters
	 *
	 * @return string The response content
	 */
	abstract protected function request($parameters);
}
