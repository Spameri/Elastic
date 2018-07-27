
# Elastic
Fetching data from ElasticSearch, examples, tests.

Why ODM?
Ne protože je rychlé.
Ne protože dělá správné dotazy do ElasticSearche.
Ale protože pracuje s objekty. Dotaz do elasticu je typovaný objekt, 
návratová hodnota z elasticu je typovaný objekt, mapovaný na postkynutou strukturu entit.

## Typy objektů

- IElasticEntity, Entita která se persistuje do elasticu, lze na ni dělat vazby
- IEntity, Entita která obsahuje další strukturu objektu
- IValue, Konkrétní konečná hodnota, obsahuje pouze jednu hodnotu

### Kolekce

- IElasticEntityCollection, extend z \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
- IEntityCollection, extend z \Spameri\Elastic\Entity\Collection\EntityCollection
- IValueCollection


