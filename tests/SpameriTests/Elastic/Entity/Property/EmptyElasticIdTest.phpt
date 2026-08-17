<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Property;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class EmptyElasticIdTest extends \Tester\TestCase
{

	public function testCreatesWithEmptyValue(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\EmptyElasticId();

		\Tester\Assert::same('', $id->value());
	}


	public function testValueReturnsEmptyString(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\EmptyElasticId();

		\Tester\Assert::same('', $id->value());
	}


	public function testThrowsOnNonEmptyString(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				new \Spameri\Elastic\Entity\Property\EmptyElasticId('non-empty');
			},
			\InvalidArgumentException::class,
		);
	}


	public function testAcceptsExplicitEmptyString(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\EmptyElasticId('');

		\Tester\Assert::same('', $id->value());
	}


	public function testImplementsElasticIdInterface(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\EmptyElasticId();

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\ElasticIdInterface::class, $id);
	}


	public function testImplementsValueInterface(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\EmptyElasticId();

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $id);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Property\EmptyElasticId::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testDefaultParameterIsEmptyString(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Property\EmptyElasticId::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();

		\Tester\Assert::same('', $parameters[0]->getDefaultValue());
	}


	public function testDistinctFromElasticId(): void
	{
		$emptyId = new \Spameri\Elastic\Entity\Property\EmptyElasticId();

		// EmptyElasticId and ElasticId are different classes
		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\EmptyElasticId::class, $emptyId);
		\Tester\Assert::false($emptyId instanceof \Spameri\Elastic\Entity\Property\ElasticId);
	}

}

(new EmptyElasticIdTest())->run();
