# Define structure in neon file
TODO convert to video example

- Create neon file for example in `app/ProductModule/Config/Product.neon`
- Import created file to your application config neon. Usually located in`app/config/config.neon`.  
```neon
includes:
	- ../ProducModule/Config/Product.neon
``` 
- Now you can configure your entity. Each entity has own ElasticSearch type and index with same name. 
You have to specify only index, type has same name and is only one in index, it is defined in library so you don't 
have to worry about it because of type deprecation. More details here https://www.elastic.co/guide/en/elasticsearch/reference/master/removal-of-types.html
- Entity definition is in neon under namespace `elasticSearch.entities.EntityName` 
continuing our example in file `app/ProductModule/Config/Product.neon`:
```neon
elasticSearch:
	entities:
		Product:
			index: shop_product
```
- You can specify if index has strictly defined mapping. Documentation https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic.html .
- In ElasticSearch all data indexed has to have specified mapping if not provided then ElasticSearch tries to guess type
from data provided. This is very handy but not always needed and without mistakes. So you can set index mappings as strict. 
This means newly introduced fields not specified in mapping will throw error when trying to index. Now you can manage
all fields introduced and specify their type. But if your application can add fields as needed you need to remember this
strict limitation or just do not enable it.
```neon
elasticSearch:
	entities:
		Product:
			dynamic: strict
```
- Now to specify entity mapping. Each object or encapsulation of sub fields stars with `properties:` then property name
and under it you can specify type and analyzer. 
```neon
elasticSearch:
	entities:
		Product:
			properties: 
				name:
					type: text
					analyzer: czechDictionary
```
- ElasticSearch field datatypes https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
- Available defined analyzers: `defaultAnalyzer`,  `czechDictionary`, `czechStemmer`, `czechSynonym`
- ElasticSearch default analyzers: https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-analyzers.html
- Subfields example:
```neon
elasticSearch:
	entities:
		Product:
			properties: 
				name:
					type: text
					analyzer: czechDictionary
				
				details:
					properties:
						productTag:
							type: text

						accessories:
							properties:
								name:
									type: text

								category:
									type: text
```
- ElasticSearch does not have array field type instead you can write type of array value. This means all fields can be
array of values. In this example product has field `productTag` which has array of text values.
- Object type of field is not specified directly, you can just pile fields under it. In this case it is represented by
`details` or `accessories` field. 
- Accessories can have multiple values of `accesory` which is object with two fields. More details in entity creation. 

#### Tips
- Do not use field named `id` in mapping. Use `databaseId` or `externalId` depending from your data originates.
- Do not use same field name in entity structure. 