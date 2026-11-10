<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class IndexAlreadyExistsTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\IndexAlreadyExists('test-index');
			},
			\Spameri\Elastic\Exception\IndexAlreadyExists::class,
		);
	}


	public function testContainsIndexNameInMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('my-index');

		\Tester\Assert::contains('my-index', $exception->getMessage());
	}


	public function testContainsAlreadyExistingText(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('test-index');

		\Tester\Assert::contains('already existing', $exception->getMessage());
	}


	public function testContainsDeleteHint(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('test-index');

		\Tester\Assert::contains('-f option', $exception->getMessage());
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testMessageFormat(): void
	{
		$exception = new \Spameri\Elastic\Exception\IndexAlreadyExists('products-2024');
		$message = $exception->getMessage();

		\Tester\Assert::contains('You are trying to create already existing index', $message);
		\Tester\Assert::contains('products-2024', $message);
	}

}

(new IndexAlreadyExistsTest())->run();
