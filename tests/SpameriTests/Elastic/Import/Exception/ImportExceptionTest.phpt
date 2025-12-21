<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Exception;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ImportExceptionTest extends \Tester\TestCase
{

	public function testImportExceptionExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\ImportException('Test message');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testImportExceptionMessage(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\ImportException('Import failed');

		\Tester\Assert::same('Import failed', $exception->getMessage());
	}


	public function testImportExceptionCode(): void
	{
		$exception = new \Spameri\Elastic\Import\Exception\ImportException('Error', 500);

		\Tester\Assert::same(500, $exception->getCode());
	}


	public function testImportExceptionPreviousException(): void
	{
		$previous = new \Exception('Previous error');
		$exception = new \Spameri\Elastic\Import\Exception\ImportException('Import error', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}

}

(new ImportExceptionTest())->run();
