<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Person;

class CharacterCollectionElastic extends \Spameri\Elastic\Entity\Collection\AbstractEntityCollection
{

	public function character(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId|null $episode = null,
	): Character
	{
		/** @var \SpameriTests\Elastic\Data\Entity\Person\Character $character */
		foreach ($this->collection() as $character) {
			if ($episode) {
				if (
					$character->id === $id->value()
					&& $character->episode->value() === $episode->value()
				) {
					return $character;
				}

			} else {
				if ($character->id === $id->value()) {
					return $character;
				}
			}
		}

		throw new \Nette\InvalidStateException(
			'Character in video: ' . $id->value() . ' not found.',
		);
	}

}
