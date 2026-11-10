<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Property;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ElasticIdTest extends \Tester\TestCase
{

	public function testCreateFromString(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('abc123');

		\Tester\Assert::same('abc123', $id->value());
	}


	public function testValueReturnsId(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123');

		\Tester\Assert::same('test-id-123', $id->value());
	}


	public function testThrowsOnEmptyString(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				new \Spameri\Elastic\Entity\Property\ElasticId('');
			},
			\InvalidArgumentException::class,
		);
	}


	public function testAcceptsUuid(): void
	{
		$uuid = '550e8400-e29b-41d4-a716-446655440000';
		$id = new \Spameri\Elastic\Entity\Property\ElasticId($uuid);

		\Tester\Assert::same($uuid, $id->value());
	}


	public function testAcceptsNumericString(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('12345');

		\Tester\Assert::same('12345', $id->value());
	}


	public function testAcceptsSpecialCharacters(): void
	{
		$specialId = 'id_with-special.chars:123';
		$id = new \Spameri\Elastic\Entity\Property\ElasticId($specialId);

		\Tester\Assert::same($specialId, $id->value());
	}


	public function testImplementsElasticIdInterface(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('test');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\ElasticIdInterface::class, $id);
	}


	public function testImplementsValueInterface(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('test');

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $id);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Property\ElasticId::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testFieldNameConstant(): void
	{
		\Tester\Assert::same('_id', \Spameri\Elastic\Entity\Property\ElasticId::FIELD_NAME);
	}


	public function testValueReturnsString(): void
	{
		$id = new \Spameri\Elastic\Entity\Property\ElasticId('test-id');

		\Tester\Assert::type('string', $id->value());
	}

}

(new ElasticIdTest())->run();
