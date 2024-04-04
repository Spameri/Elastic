<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class VideoFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Model\PersonService $personService,
	)
	{
	}


	/**
	 * @return \Generator<\SpameriTests\Elastic\Data\Entity\Video>
	 */
	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string|null $class = null,
	): \Generator
	{
		yield new \SpameriTests\Elastic\Data\Entity\Video(
			new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
			new \SpameriTests\Elastic\Data\Entity\Video\Identification(
				new \SpameriTests\Elastic\Data\Entity\Property\ImdbId($hit->getValue('identification.imdb')),
			),
			new \SpameriTests\Elastic\Data\Entity\Property\Name($hit->getValue('name')),
			new \SpameriTests\Elastic\Data\Entity\Property\Year($hit->getValue('year')),
			new \SpameriTests\Elastic\Data\Entity\Video\Technical(),
			new \SpameriTests\Elastic\Data\Entity\Video\Story(
				new \SpameriTests\Elastic\Data\Entity\Property\Description($hit->getValue('story.description')),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection(
					new \SpameriTests\Elastic\Data\Entity\Video\Story\TagLine($hit->getValue('story.tagline')),
				),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis($hit->getValue('synopsis')),
			),
			new \SpameriTests\Elastic\Data\Entity\Video\Details(
				new \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic(),
				new \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings(
					new \SpameriTests\Elastic\Data\Entity\Video\Details\RatingsCount(1000),
				),
			),
			new \SpameriTests\Elastic\Data\Entity\Video\HighLights(
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection(),
				new \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection(),
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
				new \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection(),
			),
			new \SpameriTests\Elastic\Data\Entity\Video\People($this->personService),
			new \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection(),
		);
	}

}
