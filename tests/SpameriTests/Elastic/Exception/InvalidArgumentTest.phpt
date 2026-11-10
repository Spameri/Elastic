<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class InvalidArgumentTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\InvalidArgument('Invalid parameter');
			},
			\Spameri\Elastic\Exception\InvalidArgument::class,
		);
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\InvalidArgument('Test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\InvalidArgument('Test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testPreservesErrorMessage(): void
	{
		$message = 'Parameter must be a positive integer';
		$exception = new \Spameri\Elastic\Exception\InvalidArgument($message);

		\Tester\Assert::same($message, $exception->getMessage());
	}


	public function testPreservesErrorCode(): void
	{
		$exception = new \Spameri\Elastic\Exception\InvalidArgument('Test', 400);

		\Tester\Assert::same(400, $exception->getCode());
	}


	public function testPreservesPreviousException(): void
	{
		$previous = new \InvalidArgumentException('Original error');
		$exception = new \Spameri\Elastic\Exception\InvalidArgument('Test', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}


	public function testEmptyMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\InvalidArgument('');

		\Tester\Assert::same('', $exception->getMessage());
	}

}

(new InvalidArgumentTest())->run();
