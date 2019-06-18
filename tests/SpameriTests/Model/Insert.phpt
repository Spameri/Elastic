<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;


require_once __DIR__ . '/../../bootstrap.php';


class Insert extends \Tester\TestCase
{

	public function testInsert(): void
	{
		/** @var \SpameriTests\Data\Model\PersonService $personService */
		$personService = \Mockery::mock(\SpameriTests\Data\Model\PersonService::class);
		$video = new \SpameriTests\Data\Entity\Video(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			new \SpameriTests\Data\Entity\Video\Identification(
				new \SpameriTests\Data\Entity\Property\ImdbId(4154796)
			),
			new \SpameriTests\Data\Entity\Property\Name('Avengers: Endgame'),
			new \SpameriTests\Data\Entity\Property\Year(2019),
			new \SpameriTests\Data\Entity\Video\Technical(),
			new \SpameriTests\Data\Entity\Video\Story(
				new \SpameriTests\Data\Entity\Property\Description('After the devastating events of Avengers: Infinity War (2018), the universe is in ruins. With the help of remaining allies, the Avengers assemble once more in order to undo Thanos\' actions and restore order to the universe.'),
				new \SpameriTests\Data\Entity\Video\Story\TagLineCollection(
					new \SpameriTests\Data\Entity\Video\Story\TagLine('Avenge the fallen.')
				),
				new \SpameriTests\Data\Entity\Video\Story\PlotSummaryCollection(),
				new \SpameriTests\Data\Entity\Video\Story\KeyWordCollection(),
				new \SpameriTests\Data\Entity\Video\Story\Synopsis('')
			),
			new \SpameriTests\Data\Entity\Video\Details(
				new \SpameriTests\Data\Entity\Video\Details\GenreCollection(),
				new \SpameriTests\Data\Entity\Video\Details\AliasCollectionElastic(),
				new \SpameriTests\Data\Entity\Video\Details\ReleaseCollectionElastic(),
				new \SpameriTests\Data\Entity\Video\Details\Ratings(
					new \SpameriTests\Data\Entity\Video\Details\RatingsCount(1000)
				)
			),
			new \SpameriTests\Data\Entity\Video\HighLights(
				new \SpameriTests\Data\Entity\Video\HighLights\TriviaCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\GoofCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\CrazyCreditCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\QuoteCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\LocationCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\AlternateVersionCollection(),
				new \SpameriTests\Data\Entity\Video\HighLights\CompanyCreditCollection()
			),
			new \SpameriTests\Data\Entity\Video\Connections(
				new \SpameriTests\Data\Entity\Video\Connections\FollowedCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\RemadeCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\SpinOffCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\EditedIntoCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\ReferenceCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\ReferencedCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\FeaturedCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\SpoofedCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\FollowsCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\SpunOffCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\VersionOfCollection(),
				new \SpameriTests\Data\Entity\Video\Connections\EditedFromCollection()
			),
			new \SpameriTests\Data\Entity\Video\People($personService),
			new \SpameriTests\Data\Entity\Video\SeasonCollection()
		);

		/** @var \Spameri\Elastic\Model\UserProviderInterface $userProvider */
		$userProvider = \Mockery::mock(\Spameri\Elastic\Model\UserProviderInterface::class);
		/** @var \Spameri\Elastic\Model\ServiceLocator $serviceLocator */
		$serviceLocator = \Mockery::mock(\Spameri\Elastic\Model\ServiceLocator::class);
		$insert = new \Spameri\Elastic\Model\Insert(
			new \Spameri\Elastic\Model\Insert\PrepareEntityArray(
				new \Spameri\Elastic\Model\Insert\ApplyTimestamp(
					$userProvider,
					new \Spameri\Elastic\Provider\DateTimeProvider(new \DateTimeImmutable())
				),
				$serviceLocator
			),
			new \Spameri\Elastic\ClientProvider(
				new \Elasticsearch\ClientBuilder(),
				new \Spameri\Elastic\Settings\NeonSettingsProvider(\SpameriTests\Elastic\Config::HOST, 9200)
			)
		);

		\Tester\Assert::noError(static function () use ($insert, $video) {
			$id = $insert->execute($video, 'spameri_video');

			\Tester\Assert::same(20, \strlen($id));
		});
	}

}
(new Insert())->run();
