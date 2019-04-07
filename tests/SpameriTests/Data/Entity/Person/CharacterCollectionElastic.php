<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Person;


class CharacterCollectionElastic extends \Spameri\Elastic\Entity\Collection\EntityCollection
{

	public function character(
		\SpameriTests\Data\Entity\Property\ImdbId $id,
		?\SpameriTests\Data\Entity\Property\ImdbId $episode = NULL
	) : Character
	{
		/** @var \SpameriTests\Data\Entity\Person\Character $character */
		foreach ($this->collection() as $character) {
			if ($episode) {
				if (
					$character->id()->value() === $id->value()
					&& $character->episode()
					&& $character->episode()->value() === $episode->value()
				) {
					return $character;
				}

			} else {
				if ($character->id()->value() === $id->value()) {
					return $character;
				}
			}
		}

		throw new \Nette\InvalidStateException(
			'Character in video: ' . $id->value() . ' not found.'
		);
	}
}
