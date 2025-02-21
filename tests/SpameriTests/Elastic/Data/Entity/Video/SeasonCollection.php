<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

/**
 * @template-covariant T of \SpameriTests\Elastic\Data\Entity\Video\Season
 * @template-extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection<T>
 */
class SeasonCollection extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function season(\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id): Season|null
	{
		/** @var \SpameriTests\Elastic\Data\Entity\Video\Season $season */
		foreach ($this->collection() as $season) {
			if ($season->number()->value() === $id->value()) {
				return $season;
			}
		}

		return null;
	}

}
