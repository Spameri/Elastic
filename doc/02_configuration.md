## Mapping class
```php
<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class VideoMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{

	private string $index;


	public function __construct(
		string $index
	)
	{
		$this->index = $index;
	}


	public function provide(): \Spameri\ElasticQuery\Mapping\Settings
	{
		$settings = new \Spameri\ElasticQuery\Mapping\Settings($this->index);

		$nameFields = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\SubFields(
			'name',
			\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
		);
		$nameFields->addMappingField(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
				'edgeNgram',
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
			)
		);
		$nameFields->addMappingField(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
				'wordSplit',
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
			)
		);
		$nameFields->addMappingField(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
				'wordJoin',
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
			)
		);
		$settings->addMappingSubField($nameFields);

		$story = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
			'story',
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
					'description',
					\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
				),
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
					'tagLine',
					\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
				)
			)
		);
		$settings->addMappingFieldObject($story);

		$season = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
			'season',
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
					'number',
					\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
				),
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
					'episodes',
					new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
						new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
							'id',
							\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
						),
						new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
							'number',
							\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
						),
						new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
							'name',
							\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
						),
						new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
							'description',
							\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
						)
					)
				)
			)
		);
		$settings->addMappingFieldObject($season);
		
		$identification = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
		'identification',
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
					'imdb',
					\Spameri\ElasticQuery\Mapping\AllowedValues::TYPE_KEYWORD
				)
			)
		);
		$settings->addMappingFieldObject($identification);
		
		$settings->addMappingField(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
				'year',
				\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_LONG
			)
		);
		
		$settings->addMappingFieldObject(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
				'technical',
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection()
			)
		);
		
		$settings->addMappingFieldObject(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
				'details',
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection()
			)
		);
		
		$settings->addMappingFieldObject(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
				'highLights',
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection()
			)
		);
		
		$settings->addMappingFieldObject(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
				'connections',
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection()
			)
		);
		
		$settings->addMappingFieldObject(
			new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
				'people',
				new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection()
			)
		);
		
		return $settings;
	}

}

```

#### Tips
- Do not use field named `id` in mapping. Use `databaseId` or `externalId` depending from your data originates.
- Do not use same field name in entity structure. 