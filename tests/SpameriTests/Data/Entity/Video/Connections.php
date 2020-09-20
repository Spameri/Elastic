<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video;


class Connections implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\FollowedCollection
	 */
	private $followed;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\RemadeCollection
	 */
	private $remade;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\SpinOffCollection
	 */
	private $spinOff;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\EditedIntoCollection
	 */
	private $editedInto;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\ReferenceCollection
	 */
	private $reference;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\ReferencedCollection
	 */
	private $referenced;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\FeaturedCollection
	 */
	private $featured;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\SpoofedCollection
	 */
	private $spoofed;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\FollowsCollection
	 */
	private $follows;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\SpunOffCollection
	 */
	private $spunOff;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\VersionOfCollection
	 */
	private $versionOf;
	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections\EditedFromCollection
	 */
	private $editedFrom;


	public function __construct(
		\SpameriTests\Data\Entity\Video\Connections\FollowedCollection $followed
		, \SpameriTests\Data\Entity\Video\Connections\RemadeCollection $remade
		, \SpameriTests\Data\Entity\Video\Connections\SpinOffCollection $spinOff
		, \SpameriTests\Data\Entity\Video\Connections\EditedIntoCollection $editedInto
		, \SpameriTests\Data\Entity\Video\Connections\ReferenceCollection $reference
		, \SpameriTests\Data\Entity\Video\Connections\ReferencedCollection $referenced
		, \SpameriTests\Data\Entity\Video\Connections\FeaturedCollection $featured
		, \SpameriTests\Data\Entity\Video\Connections\SpoofedCollection $spoofed
		, \SpameriTests\Data\Entity\Video\Connections\FollowsCollection $follows
		, \SpameriTests\Data\Entity\Video\Connections\SpunOffCollection $spunOff
		, \SpameriTests\Data\Entity\Video\Connections\VersionOfCollection $versionOf
		, \SpameriTests\Data\Entity\Video\Connections\EditedFromCollection $editedFrom
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


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{
		return (string) \spl_object_id($this);
	}


	public function followed() : \SpameriTests\Data\Entity\Video\Connections\FollowedCollection
	{
		return $this->followed;
	}


	public function remade() : \SpameriTests\Data\Entity\Video\Connections\RemadeCollection
	{
		return $this->remade;
	}


	public function spinOff() : \SpameriTests\Data\Entity\Video\Connections\SpinOffCollection
	{
		return $this->spinOff;
	}


	public function editedInto() : \SpameriTests\Data\Entity\Video\Connections\EditedIntoCollection
	{
		return $this->editedInto;
	}


	public function reference() : \SpameriTests\Data\Entity\Video\Connections\ReferenceCollection
	{
		return $this->reference;
	}


	public function referenced() : \SpameriTests\Data\Entity\Video\Connections\ReferencedCollection
	{
		return $this->referenced;
	}


	public function featured() : \SpameriTests\Data\Entity\Video\Connections\FeaturedCollection
	{
		return $this->featured;
	}


	public function spoofed() : \SpameriTests\Data\Entity\Video\Connections\SpoofedCollection
	{
		return $this->spoofed;
	}


	public function follows() : \SpameriTests\Data\Entity\Video\Connections\FollowsCollection
	{
		return $this->follows;
	}


	public function spunOff() : \SpameriTests\Data\Entity\Video\Connections\SpunOffCollection
	{
		return $this->spunOff;
	}


	public function versionOf() : \SpameriTests\Data\Entity\Video\Connections\VersionOfCollection
	{
		return $this->versionOf;
	}


	public function editedFrom() : \SpameriTests\Data\Entity\Video\Connections\EditedFromCollection
	{
		return $this->editedFrom;
	}
}
