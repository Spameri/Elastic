<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class CreateTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testProcess(): void
	{
		/** @var \Spameri\Elastic\Model\VersionProvider $versionProvider */
		$versionProvider = $this->container->getByType(\Spameri\Elastic\Model\VersionProvider::class);
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);
		/** @var \SpameriTests\Elastic\Data\Model\VideoMapping $videoMapping */
		$videoMapping = $this->container->getByType(\SpameriTests\Elastic\Data\Model\VideoMapping::class);

		$create = new \Spameri\Elastic\Model\Indices\Create(
			$this->container->getByType(\Spameri\Elastic\ClientProvider::class),
			$this->container->getByType(\Spameri\Elastic\Model\VersionProvider::class)
		);

		$create->execute(
			\SpameriTests\Elastic\Config::INDEX_CREATE,
			$videoMapping->provide()->toArray(),
			\SpameriTests\Elastic\Config::INDEX_CREATE
		);
		$response = $getMapping->execute(\SpameriTests\Elastic\Config::INDEX_CREATE);

		if ($versionProvider->provide() <= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			\Tester\Assert::true(
				isset($response[\SpameriTests\Elastic\Config::INDEX_CREATE]['mappings'][\SpameriTests\Elastic\Config::INDEX_CREATE])
			);
			$existingMapping = $response[\SpameriTests\Elastic\Config::INDEX_CREATE]['mappings'][\SpameriTests\Elastic\Config::INDEX_CREATE];

			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING,
				$existingMapping['properties']['name']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING,
				$existingMapping['properties']['name']['fields']['edgeNgram']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING,
				$existingMapping['properties']['season']['properties']['number']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING,
				$existingMapping['properties']['story']['properties']['description']['type']
			);

		} else {
			\Tester\Assert::true(
				isset($response[\SpameriTests\Elastic\Config::INDEX_CREATE]['mappings'])
			);
			$existingMapping = $response[\SpameriTests\Elastic\Config::INDEX_CREATE]['mappings'];

			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
				$existingMapping['properties']['name']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
				$existingMapping['properties']['name']['fields']['edgeNgram']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD,
				$existingMapping['properties']['season']['properties']['number']['type']
			);
			\Tester\Assert::same(
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD,
				$existingMapping['properties']['story']['properties']['description']['type']
			);
		}
	}


	protected function tearDown()
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute(\SpameriTests\Elastic\Config::INDEX_CREATE);
	}

}

(new CreateTest())->run();
