<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DocumentInsertFailedTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\DocumentInsertFailed('Insert failed');
			},
			\Spameri\Elastic\Exception\DocumentInsertFailed::class,
		);
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed('Test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed('Test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testPreservesErrorMessage(): void
	{
		$message = 'Failed to insert document with ID abc123';
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed($message);

		\Tester\Assert::same($message, $exception->getMessage());
	}


	public function testPreservesErrorCode(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed('Test', 500);

		\Tester\Assert::same(500, $exception->getCode());
	}


	public function testPreservesPreviousException(): void
	{
		$previous = new \Exception('Bulk insert error');
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed('Test', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}


	public function testCanContainDocumentId(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentInsertFailed('Insert failed for document: doc-123');

		\Tester\Assert::contains('doc-123', $exception->getMessage());
	}

}

(new DocumentInsertFailedTest())->run();
