<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class AliasAlreadyExistsTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\AliasAlreadyExists('test-alias');
			},
			\Spameri\Elastic\Exception\AliasAlreadyExists::class,
		);
	}


	public function testContainsAliasNameInMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\AliasAlreadyExists('my-alias');

		\Tester\Assert::contains('my-alias', $exception->getMessage());
	}


	public function testContainsAlreadyExistingText(): void
	{
		$exception = new \Spameri\Elastic\Exception\AliasAlreadyExists('test-alias');

		\Tester\Assert::contains('already existing alias', $exception->getMessage());
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\AliasAlreadyExists('test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\AliasAlreadyExists('test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testMessageFormat(): void
	{
		$exception = new \Spameri\Elastic\Exception\AliasAlreadyExists('products');
		$message = $exception->getMessage();

		\Tester\Assert::contains('You are trying to create already existing alias', $message);
		\Tester\Assert::contains('products', $message);
	}

}

(new AliasAlreadyExistsTest())->run();
