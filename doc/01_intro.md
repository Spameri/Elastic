# Intro

## Install

Use composer `composer require spameri/elastic`

## Usage

### 1. Config ElasticSearch

In your config neon, enable extensions. Kdyby/Console is there because we need it to do some command line commands.
Monolog is required by elasticsearch/elasticsearch and we can use existing extension in Kdyby/Monolog. 

```neon
extensions:
	elasticSearch: \Spameri\Elastic\DI\ElasticSearchExtension
	console: Kdyby\Console\DI\ConsoleExtension
	monolog: Kdyby\Monolog\DI\MonologExtension
```

Then configure where is your ElasticSearch.
```neon
elasticSearch:
	host: 127.0.0.1
	port: 9200
```

For more config options see default values in `\Spameri\Elastic\DI\ElasticSearchExtension::$defaults`. [Here](../src/DI/ElasticSearchExtension.php#L9).

#### Raw client usage
- After this configuration you are ready to use ElasticSearch in your Nette application.
- Where needed just inject `\Spameri\Elastic\ClientProvider` and then directly call what you need, like this:
```php
$result = $this->clientProvider->client()->search(
	(
		new \Spameri\ElasticQuery\Document(
			$index,
			new \Spameri\ElasticQuery\Document\Body\Plain(
				$elasticQuery->toArray()
			),
			$index
		)
	)->toArray()
);
```
- [Client](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php) is provided from **elasticsearch/elasticsearch** and you can see their [documentation](https://github.com/elastic/elasticsearch-php#quickstart) 
what methods and arrays are supported.
- When in doubt what how many arrays or how many arguments **match** supports use [Spameri/ElasticQuery](https://github.com/Spameri/ElasticQuery/blob/master/doc/02-query-objects.md)
- This is library used in later examples. But direct approach is also possible.

---

### 2. First entity

#### [Neon file configuration](02_neon_configuration.md)

#### [Create entity class](03_entity_class.md)

#### [Create entity service](12_entity_service.md)

#### [Create entity factory](11_entity_factory.md)

---

### 3. Mapping

#### [Create new index with mapping](05_new_index_with_mapping.md)

---

### 4. Fill with data

#### [Create and save entity](06_fill_data.md)

#### [Saving process explained](07_save_explained.md)

---

### 5. Get data from ElasticSearch

#### [Get data by ID](08_basic_get.md)

#### [Get data by tag](13_advanced_get.md)

---

### 6. Filter data from ElasticSearch

#### [Match data](09_match_get.md)

---

### 7. Aggregate data from ElasticSearch

#### [Aggregate data](10_aggregate.md)

---

### x. Other

#### [Data interfaces](04_data_interfaces.md)

