<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DocumentNotFoundTest extends \Tester\TestCase
{

	public function testThrownWhenDocumentMissing(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\DocumentNotFound('test-index');
			},
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testContainsIndexInMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('my-index');

		\Tester\Assert::contains('my-index', $exception->getMessage());
	}


	public function testContainsDocumentNotFoundText(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('test-index');

		\Tester\Assert::contains('not found', $exception->getMessage());
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testWithElasticQuery(): void
	{
		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('field', 'value'),
		);

		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('test-index', $query);

		\Tester\Assert::contains('test-index', $exception->getMessage());
		\Tester\Assert::contains('query', $exception->getMessage());
	}


	public function testWithoutElasticQuery(): void
	{
		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('test-index', null);

		\Tester\Assert::contains('test-index', $exception->getMessage());
	}


	public function testMessageFormatWithQuery(): void
	{
		$query = new \Spameri\ElasticQuery\ElasticQuery();

		$exception = new \Spameri\Elastic\Exception\DocumentNotFound('products', $query);
		$message = $exception->getMessage();

		\Tester\Assert::contains('Document in index "products" not found', $message);
		\Tester\Assert::contains('With query:', $message);
	}

}

(new DocumentNotFoundTest())->run();
