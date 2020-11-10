<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class Connections implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection
	 */
	private $followed;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection
	 */
	private $remade;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection
	 */
	private $spinOff;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection
	 */
	private $editedInto;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection
	 */
	private $reference;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection
	 */
	private $referenced;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection
	 */
	private $featured;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection
	 */
	private $spoofed;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection
	 */
	private $follows;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection
	 */
	private $spunOff;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection
	 */
	private $versionOf;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection
	 */
	private $editedFrom;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\Connections\FollowedCollection $followed
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\RemadeCollection $remade
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\SpinOffCollection $spinOff
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedIntoCollection $editedInto
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferenceCollection $reference
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\ReferencedCollection $referenced
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\FeaturedCollection $featured
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\SpoofedCollection $spoofed
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\FollowsCollection $follows
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\SpunOffCollection $spunOff
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\VersionOfCollection $versionOf
		, \SpameriTests\Elastic\Data\Entity\Video\Connections\EditedFromCollection $editedFrom
	)
	{
		$this->followed = $followed;
		$this->remade = $remade;
		$this->spinOff = $spinOff;
		$this->editedInto = $editedInto;
		$this->reference = $reference;
		$this->referenced = $referenced;
		$this->featured = $featured;
		$this->spoofed = $spoofed;
		$this->follows = $follows;
		$this->spunOff = $spunOff;
		$this->versionOf = $versionOf;
		$this->editedFrom = $editedFrom;
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
