<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Season;


class EpisodeCollection extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function episode(\SpameriTests\Data\Entity\Property\ImdbId $id) : ?Episode
	{
		/** @var \SpameriTests\Data\Entity\Video\Season\Episode $episode */
		foreach ($this->collection() as $episode) {
			if ($episode->id()->value() === $id->value()) {
				return $episode;
			}
		}

		return NULL;
	}
}
