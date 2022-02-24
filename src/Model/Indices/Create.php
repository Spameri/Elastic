<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class Create
{

	private \Spameri\Elastic\ClientProvider $clientProvider;

	private \Spameri\Elastic\Model\VersionProvider $versionProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Spameri\Elastic\Model\VersionProvider $versionProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->versionProvider = $versionProvider;
	}


	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $parameters,
		?string $type = NULL
	): array
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		if (
			isset($parameters['mappings']['properties'])
			&& $this->versionProvider->provide() < \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7
		) {
			foreach ($parameters['mappings']['properties'] as $fieldName => $field) {
				$this->replaceKeywordInOlderVersion($field);
				$parameters['mappings']['properties'][$fieldName] = $field;
			}
			$parameters['mappings'] = [
				$type => $parameters['mappings'],
			];
		}

		try {
			return $this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($parameters)
					)
				)->toArray()
			)
				;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
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
				], TRUE
			)
		) {
			$field['type'] = \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_STRING;
		}
	}

}
