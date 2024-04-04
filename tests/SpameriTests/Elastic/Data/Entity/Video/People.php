<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class People extends \Spameri\Elastic\Entity\Collection\AbstractElasticEntityCollection
{

	public function personByImdb(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $imdb,
	): \SpameriTests\Elastic\Data\Entity\Person|null
	{
		/** @var \SpameriTests\Elastic\Data\Entity\Person $entity */
		foreach ($this->collection() as $entity) {
			if ($imdb->value() === $entity->identification->imdb->value()) {
				return $entity;
			}
		}

		return NULL;
	}

}
