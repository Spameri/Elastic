<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Details implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection<\SpameriTests\Elastic\Data\Entity\Video\Details\Genre> $genres
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic<\SpameriTests\Elastic\Data\Entity\Video\Details\Alias> $aliases
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic<\SpameriTests\Elastic\Data\Entity\Video\Details\Release> $releases
	 */
	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Video\Details\GenreCollection $genres,
		public \SpameriTests\Elastic\Data\Entity\Video\Details\AliasCollectionElastic $aliases,
		public \SpameriTests\Elastic\Data\Entity\Video\Details\ReleaseCollectionElastic $releases,
		public \SpameriTests\Elastic\Data\Entity\Video\Details\Ratings $ratings,
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
