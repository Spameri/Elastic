<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Exception;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class AlreadyLockedTest extends \Tester\TestCase
{

	public function testAlreadyLockedExtendsImportException(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\AlreadyLocked('Import already running');

		\Tester\Assert::type(\Spameri\Elastic\Import\Exception\ImportException::class, $exception);
	}


	public function testAlreadyLockedMessage(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\AlreadyLocked('Another import is in progress');

		\Tester\Assert::same('Another import is in progress', $exception->getMessage());
	}


	public function testAlreadyLockedIsThrowable(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\AlreadyLocked('Locked');

		\Tester\Assert::type(\Throwable::class, $exception);
	}


	public function testAlreadyLockedCanBeCaughtAsImportException(): void
	{
		$caught = false;

		try {
			throw new \Spameri\Elastic\Import\Exception\AlreadyLocked('Test');
		} catch (\Spameri\Elastic\Import\Exception\ImportException $e) {
			$caught = true;
		}

		\Tester\Assert::true($caught);
	}


	public function testAlreadyLockedUsedForConcurrentImports(): void
	{
		// AlreadyLocked is thrown when another import process holds the lock
		$exception = new \Spameri\Elastic\Import\Exception\AlreadyLocked('Lock file exists, import already running');

		\Tester\Assert::contains('Lock', $exception->getMessage());
	}

}

(new AlreadyLockedTest())->run();
