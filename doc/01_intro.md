# Intro

## Install

Use composer `composer require spameri/elastic`

## Usage

### 1. Config ElasticSearch

In your config neon, enable extensions. Kdyby/Console is there because we need it to do some command line commands.
Monolog is required by elasticsearch/elasticsearch and we can use existing extension in Kdyby/Monolog. 

```
extensions:
	elasticSearch: \Spameri\Elastic\DI\ElasticSearchExtension
	console: Kdyby\Console\DI\ConsoleExtension
	monolog: Kdyby\Monolog\DI\MonologExtension
```

Then configure where is your ElasticSearch.
```
elasticSearch:
	host: 127.0.0.1
	port: 9200
```

For more config options see default values in `\Spameri\Elastic\DI\ElasticSearchExtension::$defaults`.

### 2. First entity

#### [Neon file configuration](../blob/master/doc/02_neon_configuration.md)

#### [Create Entity class](../blob/master/doc/03_entity_class.md)

### 3. Mapping

#### [Create new index with mapping]((../blob/master/doc/05_new_index_with_mapping.md))

### 4. Fill with data
TODO

### 5. Get data from ElasticSearch
TODO Tady factories
TODO
TODO
TODO
TODO

### 6. Filter data from ElasticSearch
TODO

### 7. Aggregate data from ElasticSearch
TODO

### x. Other

#### [Data interfaces]((../blob/master/doc/04_data_interfaces.md))

