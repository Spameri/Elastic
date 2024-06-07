<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Connections implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection $followed,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection $remade,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection $spinOff,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection $editedInto,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection $reference,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection $referenced,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection $featured,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection $spoofed,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection $follows,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection $spunOff,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection $versionOf,
		private \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection $editedFrom,
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


	public function followed(): \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection
	{
		return $this->followed;
	}


	public function remade(): \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection
	{
		return $this->remade;
	}


	public function spinOff(): \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection
	{
		return $this->spinOff;
	}


	public function editedInto(): \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection
	{
		return $this->editedInto;
	}


	public function reference(): \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection
	{
		return $this->reference;
	}


	public function referenced(): \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection
	{
		return $this->referenced;
	}


	public function featured(): \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection
	{
		return $this->featured;
	}


	public function spoofed(): \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection
	{
		return $this->spoofed;
	}


	public function follows(): \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection
	{
		return $this->follows;
	}


	public function spunOff(): \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection
	{
		return $this->spunOff;
	}


	public function versionOf(): \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection
	{
		return $this->versionOf;
	}


	public function editedFrom(): \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection
	{
		return $this->editedFrom;
	}

}
