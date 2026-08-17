<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Exception;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class SettingsNotLocatedTest extends \Tester\TestCase
{

	public function testCanBeThrown(): void
	{
		\Tester\Assert::exception(
			static function (): void {
				throw new \Spameri\Elastic\Exception\SettingsNotLocated('test-index');
			},
			\Spameri\Elastic\Exception\SettingsNotLocated::class,
		);
	}


	public function testContainsIndexNameInMessage(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('my-index');

		\Tester\Assert::contains('my-index', $exception->getMessage());
	}


	public function testContainsNotFoundText(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('test-index');

		\Tester\Assert::contains('not found', $exception->getMessage());
	}


	public function testContainsSettingsText(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('test-index');

		\Tester\Assert::contains('Settings', $exception->getMessage());
	}


	public function testExtendsAbstractElasticSearchException(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('test');

		\Tester\Assert::type(\Spameri\Elastic\Exception\AbstractElasticSearchException::class, $exception);
	}


	public function testExtendsRuntimeException(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('test');

		\Tester\Assert::type(\RuntimeException::class, $exception);
	}


	public function testMessageFormat(): void
	{
		$exception = new \Spameri\Elastic\Exception\SettingsNotLocated('products');
		$message = $exception->getMessage();

		\Tester\Assert::same('Settings not found for index name products', $message);
	}

}

(new SettingsNotLocatedTest())->run();
