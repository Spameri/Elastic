# Insert explained

## \Spameri\Elastic\Model\Insert\PrepareEntityArray
- This class is responsible for converting ElasticSearch entity to array that can be then saved to ElasticSearch.
- Configuring is done by implementing [interfaces](04_data_interfaces.md), no need for annotations or neon.

### ::prepare(\Spameri\Elastic\Entity\ElasticEntityInterface $entity)
- This method is here for last before convert modifications.
- Then calling iterateVariables to prepare rest of entity array.

### ::iterateVariables(array $variables)
This method accepts entity variables and based on their type performs converting to array to ready entity for insert.
There are 9 types of property handled:
1. **\Spameri\Elastic\Entity\ElasticEntityInterface** in this case service locator comes to play and locates service for related
entity and saves connected entity. And prepares related entity's id to property. Because to ElasticSearch goes only 
string id.

2. **\Spameri\Elastic\Entity\EntityInterface** this is structural entity and is saved directly to parent entity. Its properties
are iterated with `::iterateVariables($property->entityVariables())`

3. **\Spameri\Elastic\Entity\ValueInterface** raw value in object, directly converted to array.

4. **\Spameri\Elastic\Entity\EntityCollectionInterface** Collection of structural class **EntityInterface**, iterate and act as step 2.

5. **\Spameri\Elastic\Entity\ElasticEntityCollectionInterface** Collection of ElasticSearch entities **ElasticEntityInterface**, 
iterate and act as step 1.

6. **\Spameri\Elastic\Entity\ValueCollectionInterface** Collection of **ValueInterface**, iterate and act as step 3. 

7. Scalar values **string**, **int**, **bool** or **NULL**, no action just pass to array.

8. **\Spameri\Elastic\Entity\DateTimeInterface** Date interface with specified format by this library so ElasticSearch
can save it without problems.
- **\Spameri\Elastic\Entity\Property\Date** for `Y-m-d` format
- **\Spameri\Elastic\Entity\Property\DateTime** for `Y-m-d\TH:i:s` format

9. **\DateTime** All other Dates are converted to `Y-m-d\TH:i:s`

10. Exception thrown property is none of above. 
