<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class EntityIsNotValidTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\EntityIsNotValid('Entity validation failed');
			},
			\Spameri\Elastic\Exception\EntityIsNotValid::class,
		);
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testPreservesErrorMessage(): void
	{
		$message = 'Entity "Product" is missing required field "name"';
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid($message);

		\Tester\Assert::same($message, $exception->getMessage());
	}


	public function testPreservesErrorCode(): void
	{
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Test', 422);

		\Tester\Assert::same(422, $exception->getCode());
	}


	public function testPreservesPreviousException(): void
	{
		$previous = new \InvalidArgumentException('Missing required field');
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Test', 0, $previous);

		\Tester\Assert::same($previous, $exception->getPrevious());
	}


	public function testCanContainEntityClassName(): void
	{
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Entity App\Entity\Product is not valid');

		\Tester\Assert::contains('App\Entity\Product', $exception->getMessage());
	}


	public function testCanContainFieldName(): void
	{
		$exception = new \Spameri\Elastic\Exception\EntityIsNotValid('Field "price" must be a positive number');

		\Tester\Assert::contains('price', $exception->getMessage());
	}

}

(new EntityIsNotValidTest())->run();
