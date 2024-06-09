<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Season;

class EpisodeCollection extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function episode(\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id): Episode|null
	{
		/** @var \SpameriTests\Elastic\Data\Entity\Video\Season\Episode $episode */
		foreach ($this->collection() as $episode) {
			if ($episode->id()->value() === $id->value()) {
				return $episode;
			}
		}

		return null;
	}

}
