<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ScrollNotInitializedTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\ScrollNotInitialized('Scroll not initialized');
			},
			\Spameri\Elastic\Exception\ScrollNotInitialized::class,
		);
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized('Test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized('Test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testPreservesErrorMessage(): void
	{
		$message = 'Scroll must be initialized before calling next()';
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized($message);

		\Tester\Assert::same($message, $exception->getMessage());
	}


	public function testPreservesErrorCode(): void
	{
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized('Test', 400);

		\Tester\Assert::same(400, $exception->getCode());
	}


	public function testPreservesPreviousException(): void
	{
		$previous = new \Exception('No scroll context');
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized('Test', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}


	public function testEmptyMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\ScrollNotInitialized('');

		\Tester\Assert::same('', $exception->getMessage());
	}

}

(new ScrollNotInitializedTest())->run();
