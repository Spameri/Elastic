<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with a collection of value objects, stored as a flat list of scalars.
 *
 * Distinct from EntityWithCollection, whose members are nested objects with
 * properties of their own: a value collection round-trips through
 * ["Action", "Drama"], not through a list of documents.
 */
class EntityWithValueCollection extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection<\SpameriTests\Elastic\Data\Entity\Video\Details\Genre> $genres
	 */
	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		#[\Spameri\Elastic\Mapping\ValueCollection(class: \SpameriTests\Elastic\Data\Entity\Video\Details\Genre::class)]
		public \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection $genres,
	)
	{
		parent::__construct($id);
	}

}
