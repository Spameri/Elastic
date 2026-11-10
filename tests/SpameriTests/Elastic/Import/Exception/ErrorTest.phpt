<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Exception;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ErrorTest extends \Tester\TestCase
{

	public function testErrorExtendsImportException(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Error('API error');

		\Tester\Assert::type(\Spameri\Elastic\Import\Exception\ImportException::class, $exception);
	}


	public function testErrorMessage(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Error('API is not responding');

		\Tester\Assert::same('API is not responding', $exception->getMessage());
	}


	public function testErrorIsThrowable(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Error('Error');

		\Tester\Assert::type(\Throwable::class, $exception);
	}


	public function testErrorCanBeCaughtAsImportException(): void
	{
		$caught = false;

		try {
			throw new \Spameri\Elastic\Import\Exception\Error('Test');
		} catch (\Spameri\Elastic\Import\Exception\ImportException $e) {
			$caught = true;
		}

		\Tester\Assert::true($caught);
	}


	public function testErrorWithPreviousException(): void
	{
		$apiError = new \RuntimeException('Connection timeout');
		$exception = new \Spameri\Elastic\Import\Exception\Error('API error', 0, $apiError);

		\Tester\Assert::same($apiError, $exception->getPrevious());
	}

}

(new ErrorTest())->run();
