<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Create
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $parameters,
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($parameters),
					)
				)->toArray(),
			)->asArray()
				;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}


	public function replaceKeywordInOlderVersion(array &$field): void
	{
		if (isset($field['properties'])) {
			foreach ($field['properties'] as $propertyKey => $propertyField) {
				$this->replaceKeywordInOlderVersion($propertyField);
				$field['properties'][$propertyKey] = $propertyField;
			}
		}

		if (isset($field['fields'])) {
			foreach ($field['fields'] as $fieldKey => $subField) {
				$this->replaceKeywordInOlderVersion($subField);
				$field['fields'][$fieldKey] = $subField;
			}
		}

		if (isset($field['type']) === FALSE) {
			return;
		}

		if (
			\in_array(
				\strtolower($field['type']), [
					\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT,
					\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD,
				], TRUE,
			)
		) {
			$field['type'] = \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING;
		}
	}

}
