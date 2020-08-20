<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video;


class People extends \Spameri\Elastic\Entity\Collection\AbstractElasticEntityCollection
{

	public function personByImdb(
		\SpameriTests\Data\Entity\Property\ImdbId $imdb
	) : ?\SpameriTests\Data\Entity\Person
	{
		/** @var \SpameriTests\Data\Entity\Person $entity */
		foreach ($this->collection() as $entity) {
			if ($imdb->value() === $entity->identification()->imdb()->value()) {
				return $entity;
			}
		}

		return NULL;
	}
}
