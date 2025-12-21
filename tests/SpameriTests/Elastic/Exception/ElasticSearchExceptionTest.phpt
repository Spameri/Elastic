<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ElasticSearchExceptionTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\ElasticSearch('Test error');
			},
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\ElasticSearch('Test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\ElasticSearch('Test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testPreservesErrorMessage(): void
	{
		$message = 'Elasticsearch connection failed';
		$exception = new \Spameri\Elastic\Exception\ElasticSearch($message);

		\Tester\Assert::same($message, $exception->getMessage());
	}


	public function testPreservesErrorCode(): void
	{
		$exception = new \Spameri\Elastic\Exception\ElasticSearch('Test', 500);

		\Tester\Assert::same(500, $exception->getCode());
	}


	public function testPreservesPreviousException(): void
	{
		$previous = new \RuntimeException('Previous error');
		$exception = new \Spameri\Elastic\Exception\ElasticSearch('Test', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}


	public function testCanWrapClientException(): void
	{
		$clientException = new \Exception('Client connection error', 503);
		$exception = new \Spameri\Elastic\Exception\ElasticSearch(
			'ElasticSearch error: ' . $clientException->getMessage(),
			$clientException->getCode(),
			$clientException,
		);

		\Tester\Assert::contains('Client connection error', $exception->getMessage());
		\Tester\Assert::same(503, $exception->getCode());
		\Tester\Assert::same($clientException, $exception->getPrevious());
	}


	public function testEmptyMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\ElasticSearch('');

		\Tester\Assert::same('', $exception->getMessage());
	}

}

(new ElasticSearchExceptionTest())->run();
