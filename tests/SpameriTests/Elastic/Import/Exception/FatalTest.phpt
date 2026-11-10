<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Exception;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class FatalTest extends \Tester\TestCase
{

	public function testFatalExtendsImportException(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Fatal('Fatal error');

		\Tester\Assert::type(\Spameri\Elastic\Import\Exception\ImportException::class, $exception);
	}


	public function testFatalMessage(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Fatal('Runner cannot continue');

		\Tester\Assert::same('Runner cannot continue', $exception->getMessage());
	}


	public function testFatalIsThrowable(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Fatal('Fatal');

		\Tester\Assert::type(\Throwable::class, $exception);
	}


	public function testFatalCanBeCaughtAsImportException(): void
	{
		$caught = false;

		try {
			throw new \Spameri\Elastic\Import\Exception\Fatal('Test');
		} catch (\Spameri\Elastic\Import\Exception\ImportException $e) {
			$caught = true;
		}

		\Tester\Assert::true($caught);
	}


	public function testFatalWithCode(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Fatal('Fatal error', 999);

		\Tester\Assert::same(999, $exception->getCode());
	}

}

(new FatalTest())->run();
