<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video;


class SeasonCollection extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function season(\SpameriTests\Data\Entity\Property\ImdbId $id) : ?Season
	{
		/** @var \SpameriTests\Data\Entity\Video\Season $season */
		foreach ($this->collection() as $season) {
			if ($season->number()->value() === $id->value()) {
				return $season;
			}
		}

		return NULL;
	}
}
