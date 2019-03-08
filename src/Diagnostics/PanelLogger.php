<?php declare(strict_types = 1);

namespace Spameri\Elastic\Diagnostics;


class PanelLogger implements \Psr\Log\LoggerInterface
{

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;
	/**
	 * @var array
	 */
	private $queries = [];
	/**
	 * @var array
	 */
	private $requestBodies = [];
	/**
	 * @var array
	 */
	private $responseBodies = [];


	public function __construct(
		\Psr\Log\LoggerInterface $logger
	)
	{
		$this->logger = $logger;
	}


	public function emergency(
		$message,
		array $context = []
	) : void
	{
		$this->logger->emergency($message, $context);
		$this->logQuery($context);
	}


	public function alert(
		$message,
		array $context = []
	) : void
	{
		$this->logger->alert($message, $context);
		$this->logQuery($context);
	}


	public function critical(
		$message,
		array $context = []
	) : void
	{
		$this->logger->critical($message, $context);
		$this->logQuery($context);
	}


	public function error(
		$message,
		array $context = []
	) : void
	{
		$this->logger->error($message, $context);
		$this->logQuery($context);
	}


	public function warning(
		$message,
		array $context = []
	) : void
	{
		$this->logger->warning($message, $context);
		$this->logQuery($context);
	}


	public function notice(
		$message,
		array $context = []
	) : void
	{
		$this->logger->notice($message, $context);
		$this->logQuery($context);
	}


	public function info(
		$message,
		array $context = []
	) : void
	{
		$this->logger->info($message, $context);
		$this->logQuery($context);
	}


	public function debug(
		$message,
		array $context = []
	) : void
	{
		$this->logger->debug($message, $context);
		$this->logQuery($context);
		$this->logRequestBody($message, $context);
		$this->logResponseBody($message, $context);
	}


	public function log(
		$level,
		$message,
		array $context = []
	) : void
	{
		$this->logger->log($level, $message, $context);
		$this->logQuery($context);
	}


	public function getQueries() : array
	{
		return $this->queries;
	}


	public function getRequestBodies() : array
	{
		return $this->requestBodies;
	}


	public function getResponseBodies() : array
	{
		return $this->responseBodies;
	}


	private function logQuery(
		array $context = []
	) : void
	{
		if (isset($context['method'], $context['uri'])) {
			$this->queries[] = $context;
		}
	}


	private function logRequestBody(
		$message,
		$context
	) : void
	{
		if (
			$message === 'Request Body'
			|| $message === 'Request Plain'
		) {
			$this->requestBodies[] = $context;
		}
	}


	private function logResponseBody(
		$message,
		$context
	) : void
	{
		if ($message === 'Response') {
			$this->responseBodies[] = $context;
		}
	}

}
