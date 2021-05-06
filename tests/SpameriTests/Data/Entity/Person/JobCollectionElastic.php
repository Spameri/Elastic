<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Person;


class JobCollectionElastic extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function job(
		\SpameriTests\Data\Entity\Property\ImdbId $imdbId,
		?\SpameriTests\Data\Entity\Property\ImdbId $episode = NULL
	) : Job
	{
		/** @var \SpameriTests\Data\Entity\Person\Job $job */
		foreach ($this->collection() as $job) {
			if ($episode) {
				if (
					$job->id()->value() === $imdbId->value()
					&& $job->episode() instanceof \SpameriTests\Data\Entity\Property\ImdbId
					&& $job->episode()->value() === $episode->value()
				) {
					return $job;
				}

			} else {
				if ($job->id()->value() === $imdbId->value()) {
					return $job;
				}
			}
		}

		throw new \Nette\InvalidStateException(
			'Job in video: ' . $imdbId->value() . ' not found.'
		);
	}

}
