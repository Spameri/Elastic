<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class VersionProviderTest extends \Tester\TestCase
{

	public function testDefaultVersion(): void
	{
		$versionProvider = new \Spameri\Elastic\Model\VersionProvider();

		\Tester\Assert::same(
			\Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_8,
			$versionProvider->provide(),
		);
	}


	public function testCustomVersion(): void
	{
		$versionProvider = new \Spameri\Elastic\Model\VersionProvider(7);

		\Tester\Assert::same(7, $versionProvider->provide());
	}


	public function testProvideReturnsInteger(): void
	{
		$versionProvider = new \Spameri\Elastic\Model\VersionProvider();

		\Tester\Assert::type('int', $versionProvider->provide());
	}

}

(new VersionProviderTest())->run();
