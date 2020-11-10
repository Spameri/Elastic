<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;


require_once __DIR__ . '/../../../bootstrap.php';


class Insert extends \Tester\TestCase
{

	public function testInsert(): void
	{
		/** @var \SpameriTests\Elastic\Data\Model\PersonService $personService */
		$personService = \Mockery::mock(\SpameriTests\Elastic\Data\Model\PersonService::class);
		$video = new \SpameriTests\Elastic\Data\Entity\Video(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			new \SpameriTests\Elastic\Data\Entity\Video\Identification(
				new \SpameriTests\Elastic\Data\Entity\Property\ImdbId(4154796)
			),
			new \SpameriTests\Elastic\Data\Entity\Property\Name('Avengers: Endgame'),
			new \SpameriTests\Elastic\Data\Entity\Property\Year(2019),
			new \SpameriTests\Elastic\Data\Entity\Video\Technical(),
			new \SpameriTests\Elastic\Data\Entity\Video\Story(
				new \SpameriTests\Elastic\Data\Entity\Property\Description('After the devastating events of Avengers: Infinity War (2018), the universe is in ruins. With the help of remaining allies, the Avengers assemble once more in order to undo Thanos\' actions and restore order to the universe.'),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection(
					new \SpameriTests\Elastic\Data\Entity\Video\Story\TagLine('Avenge the fallen.')
				),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis('')
			),
			new \SpameriTests\Elastic\Data\Entity\Video\Details(
				new \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings(
					new \SpameriTests\Elastic\Data\Entity\Video\Details\RatingsCount(1000)
				)
			),
			new \SpameriTests\Elastic\Data\Entity\Video\HighLights(
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection()
			),
			new \SpameriTests\Elastic\Data\Entity\Video\Connections(
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection()
			),
			new \SpameriTests\Elastic\Data\Entity\Video\People($personService),
			new \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection()
		);

		/** @var \Spameri\Elastic\Model\ServiceLocator $serviceLocator */
		$serviceLocator = \Mockery::mock(\Spameri\Elastic\Model\ServiceLocator::class);
		$insert = new \Spameri\Elastic\Model\Insert(
			new \Spameri\Elastic\Model\Insert\PrepareEntityArray(
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
