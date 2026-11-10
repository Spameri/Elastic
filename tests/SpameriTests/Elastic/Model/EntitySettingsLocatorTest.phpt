<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class EntitySettingsLocatorTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testLocateByIndexName(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		$settings = $locator->locate(\SpameriTests\Elastic\Config::INDEX_TITLE);

		\Tester\Assert::type(\Spameri\ElasticQuery\Mapping\Settings::class, $settings);
		\Tester\Assert::contains(\SpameriTests\Elastic\Config::INDEX_TITLE, $settings->indexName());
	}


	public function testLocateByEntityClass(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		$settings = $locator->locateByEntityClass(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::type(\Spameri\ElasticQuery\Mapping\Settings::class, $settings);
	}


	public function testLocateByEntityClassReturnsCorrectIndex(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		$titleSettings = $locator->locateByEntityClass(\SpameriTests\Elastic\Data\Entity\Title::class);
		$imageSettings = $locator->locateByEntityClass(\SpameriTests\Elastic\Data\Entity\Image::class);

		// Different entities should map to different indexes
		\Tester\Assert::contains('title', $titleSettings->indexName());
		\Tester\Assert::contains('image', $imageSettings->indexName());
	}


	public function testLocateMissingSettingsThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		\Tester\Assert::exception(
			static fn () => $locator->locate('nonexistent_index_12345'),
			\Spameri\Elastic\Exception\SettingsNotLocated::class,
		);
	}


	public function testLocateByEntityClassMissingThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		\Tester\Assert::exception(
			static fn () => $locator->locateByEntityClass(\stdClass::class),
			\Spameri\Elastic\Exception\SettingsNotLocated::class,
		);
	}


	public function testLocateAllReturnsGenerator(): void
	{
		/** @var \Spameri\Elastic\Model\EntitySettingsLocator $locator */
		$locator = $this->container->getByType(\Spameri\Elastic\Model\EntitySettingsLocator::class);

		$all = $locator->locateAll();

		\Tester\Assert::type(\Generator::class, $all);

		$count = 0;
		foreach ($all as $config) {
			\Tester\Assert::type(\Spameri\Elastic\Settings\IndexConfigInterface::class, $config);
			$count++;
		}

		// Should have at least some configs registered
		\Tester\Assert::true($count > 0);
	}

}

(new EntitySettingsLocatorTest())->run();
