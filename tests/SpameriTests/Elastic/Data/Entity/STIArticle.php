<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Article child entity for STI testing.
 * Extends STIContent with article-specific properties.
 */
class STIArticle extends STIContent
{

	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		string $title,
		string $description,
		public string $author,
		public int $wordCount,
	)
	{
		parent::__construct($id, $title, $description);
	}

}
