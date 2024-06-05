<?php declare(strict_types = 1);

namespace Spameri\Elastic\Diagnostics;

class PanelLogger implements \Psr\Log\LoggerInterface
{

	/**
	 * @var array<mixed>
	 */
	private array $queries = [];

	/**
	 * @var array<mixed>
	 */
	private array $requestBodies = [];

	/**
	 * @var array<mixed>
	 */
	private array $responseBodies = [];


	public function __construct(
		private \Psr\Log\LoggerInterface $logger,
	)
	{
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function emergency(
		$message,
		array $context = [],
	)
	{
		$this->logger->emergency($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function alert(
		$message,
		array $context = [],
	): void
	{
		$this->logger->alert($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function critical(
		$message,
		array $context = [],
	): void
	{
		$this->logger->critical($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function error(
		$message,
		array $context = [],
	): void
	{
		$this->logger->error($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function warning(
		$message,
		array $context = [],
	): void
	{
		$this->logger->warning($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function notice(
		$message,
		array $context = [],
	): void
	{
		$this->logger->notice($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function info(
		$message,
		array $context = [],
	): void
	{
		$this->logger->info($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function debug(
		$message,
		array $context = [],
	): void
	{
		$this->logger->debug($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param mixed $level
	 * @param string $message
	 * @param array<mixed> $context
	 */
	public function log(
		$level,
		$message,
		array $context = [],
	): void
	{
		$this->logger->log($level, $message, $context);
		$this->logQuery($context);
	}


	/**
	 * @return array<mixed>
	 */
	public function getQueries(): array
	{
		return $this->queries;
	}


	/**
	 * @return array<mixed>
	 */
	public function getRequestBodies(): array
	{
		return $this->requestBodies;
	}


	/**
	 * @return array<mixed>
	 */
	public function getResponseBodies(): array
	{
		return $this->responseBodies;
	}


	/**
	 * @param array<mixed> $context
	 */
	private function logQuery(
		array $context = [],
	): void
	{
		if (isset($context['request']) === true) {
			/** @var \GuzzleHttp\Psr7\Request $request */
			$request = $context['request'];
			$contents = $request->getBody()->getContents();
			if ($contents === '') {
				$contents = '{}';
			}
			$query = [
				'uri' => $request->getUri()->getPath(),
				'requestBody' =>
					\Tracy\Dumper::toHtml(
						\Nette\Utils\Json::decode($contents, \JSON_OBJECT_AS_ARRAY),
						[
							\Tracy\Dumper::DEPTH => 30,
						],
					),
				'requestBodyString' => $contents,
			];

		} elseif (isset($context['response']) === true) {
			/** @var \GuzzleHttp\Psr7\Response $response */
			$response = $context['response'];
			$query = \array_pop($this->queries);
			$decoded = \Nette\Utils\Json::decode($response->getBody()->getContents(), \JSON_OBJECT_AS_ARRAY);
			$query['responseBody'] =
				\Tracy\Dumper::toHtml(
					$decoded,
					[
						\Tracy\Dumper::DEPTH => 30,
					],
				);
			$query['duration'] = $decoded['took'] ?? null;

		} else {
			return;
		}

		$this->queries[] = $query;
	}


}
