# Create index with mapping for entity

Entity is configured [link](03_entity_class.md) and defined in php [link](03_entity_class.md#L247) and next thing is to get these settings to ElasticSearch.

## Index creating

### Variant 1. - No previous data
- When to use?
- You dont have any index, your ElasticSearch installation is clean and with no index for our entities.
- How? 
- Simple neon is configured from previous [step](02_neon_configuration.md) and we just need to run command.

```bash
php www/index spameri:elastic:create-index
```

- Can be used for specific entity
```bash
php www/index spameri:elastic:create-index video
```


### Variant 2. - Deleting previous data
- When to use?
- You have existing index with outdated data with old configuration, but you dont need to keep them. This is 
usually when you are generating ElasticSearch data from another database.
- How?
- Just add -f option. First thing command does is delete old index, then creating new empty index with new settings.
```bash
php www/index spameri:elastic:create-index -f
```

- Can be used for specific entity
```bash
php www/index spameri:elastic:create-index -f video
```

### Variant 3. - Preserving previous data
- When to use?
- You dont have source for data saved in ElasticSearch.
- Best is backup your data with command. This creates bulk json document with all data from index.
```bash
php www/index spameri:elastic:dump-index
```
- With data backed up, now you can delete index and create it fresh with new mapping - following variant 2.
- Last thing is get your old data to new index wit this command.
```bash
php www/index spameri:elastic:restore-index
```
