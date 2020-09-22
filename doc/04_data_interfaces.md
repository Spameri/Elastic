# Data interfaces

## Entity

### \Spameri\Elastic\Entity\ElasticEntityInterface

- Base ElasticSearch entity encapsulation. This represents document in ElasticSearch index.

### \Spameri\Elastic\Entity\EntityInterface

- Basic building block for tree structure of ElasticSearch documents.

### \Spameri\Elastic\Entity\ValueInterface

- If you need extra layer for single field data, this is what to use. And need should be always :). Can contain validation
logic if you need to fill entities from external source for indexing to ElasticSearch. 

***

## Collections

Multiple values of same type :)

### \Spameri\Elastic\Entity\ElasticEntityCollectionInterface

- Collection for `\Spameri\Elastic\Entity\ElasticEntityInterface` when you need reference to other entities. Lazily loads 
your collection and when saving modified data it saves to their own ElasticSearch index.
- When making collection you should extend abstract collection with prepared methods `\Spameri\Elastic\Entity\Collection\ElasticEntityCollection`.

### \Spameri\Elastic\Entity\IEntityCollection

- Collection for `\Spameri\Elastic\Entity\EntityInterface`.
- When making collection you should extend abstract collection with prepared methods `\Spameri\Elastic\Entity\Collection\EntityCollection`.

### \Spameri\Elastic\Entity\ValueCollectionInterface

- Collection for `\Spameri\Elastic\Entity\ValueInterface`.