<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Exception;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class OmitTest extends \Tester\TestCase
{

	public function testOmitExtendsImportException(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Omit('Skip item');

		\Tester\Assert::type(\Spameri\Elastic\Import\Exception\ImportException::class, $exception);
	}


	public function testOmitMessage(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Omit('Product is not public');

		\Tester\Assert::same('Product is not public', $exception->getMessage());
	}


	public function testOmitIsThrowable(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\Omit('Omit');

		\Tester\Assert::type(\Throwable::class, $exception);
	}


	public function testOmitCanBeCaughtAsImportException(): void
	{
		$caught = false;

		try {
			throw new \Spameri\Elastic\Import\Exception\Omit('Skip');
		} catch (\Spameri\Elastic\Import\Exception\ImportException $e) {
			$caught = true;
		}

		\Tester\Assert::true($caught);
	}


	public function testOmitUsedForSkippingItems(): void
	{
		// Omit is used when you need to skip one item with defined parameters
		// Example: product is not public, item doesn't meet criteria, etc.
		$exception = new \Spameri\Elastic\Import\Exception\Omit('Item does not meet import criteria');

		\Tester\Assert::contains('does not meet', $exception->getMessage());
	}

}

(new OmitTest())->run();
