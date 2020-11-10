<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class Details implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection
	 */
	private $genres;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic
	 */
	private $alias;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic
	 */
	private $release;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings
	 */
	private $ratings;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection $genres
		, \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic $aliases
		, \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic $releases
		, \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings $ratings
	)
	{
		$this->genres = $genres;
		$this->alias = $aliases;
		$this->release = $releases;
		$this->ratings = $ratings;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function genres(): Details\GenreCollection
	{
		return $this->genres;
	}


	public function alias(): Details\AliasCollectionElastic
	{
		return $this->alias;
	}


	public function release(): Details\ReleaseCollectionElastic
	{
		return $this->release;
	}


	public function ratings(): \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings
	{
		return $this->ratings;
	}
}
