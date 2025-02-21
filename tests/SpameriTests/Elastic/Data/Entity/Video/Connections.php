<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Connections implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Followed> $followed
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Remade> $remade
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOff> $spinOff
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\EditedInto> $editedInto
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Reference> $reference
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Referenced> $referenced
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Featured> $featured
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Spoofed> $spoofed
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\Follows> $follows
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOff> $spunOff
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOf> $versionOf
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection<\SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFrom> $editedFrom
	 */
	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection $followed,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection $remade,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection $spinOff,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection $editedInto,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection $reference,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection $referenced,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection $featured,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection $spoofed,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection $follows,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection $spunOff,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection $versionOf,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection $editedFrom,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}

}
