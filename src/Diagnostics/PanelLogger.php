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
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function emergency(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->emergency($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function alert(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->alert($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function critical(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->critical($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function error(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->error($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function warning(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->warning($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function notice(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->notice($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function info(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->info($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function debug(
		string|\Stringable $message,
		array $context = [],
	): void
	{
		$this->logger->debug($message, $context);
		$this->logQuery($context);
	}


	/**
	 * @param mixed $level
	 * @param string|\Stringable $message
	 * @param array<mixed> $context
	 */
	public function log(
		$level,
		string|\Stringable $message,
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
