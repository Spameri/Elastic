<?php declare(strict_types = 1);

namespace Spameri\ElasticQuery\Query;

/**
 * Match all documents query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
 */
class MatchAll implements LeafQueryInterface
{

	public function __construct(
		private float $boost = 1.0,
	)
	{
	}


	public function key(): string
	{
		return 'match_all';
	}


	public function toArray(): array
	{
		if ($this->boost !== 1.0) {
			return [
				'match_all' => [
					'boost' => $this->boost,
				],
			];
		}

		return [
			'match_all' => new \stdClass(),
		];
	}

}
