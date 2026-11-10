# Comprehensive Test Plan for Spameri/Elastic

## Executive Summary

- **Current Coverage**: 8 test files covering ~5% of 141 source files
- **Target Coverage**: 80%+ code coverage on core components
- **Estimated New Tests**: ~60-70 new test files
- **Test Type Distribution**: 70% integration tests, 30% unit tests

---

## Phase 1: Core Infrastructure (Priority: Critical)

### 1.1 EntityManager - Full Coverage
**File**: `src/EntityManager.php`
**Current Coverage**: Only `persist()` and `findAll()` partially tested

| Test File | Test Cases |
|-----------|------------|
| `EntityManager/PersistTest.phpt` | - Persist new entity with EmptyElasticId<br>- Persist existing entity (update)<br>- Persist entity with nested entities<br>- Persist entity with collections<br>- Persist entity with circular references<br>- Events fired correctly (PRE_PERSIST, POST_PERSIST, POST_CREATE/UPDATE) |
| `EntityManager/FindTest.phpt` | - `find()` by ID returns entity<br>- `find()` with non-existent ID returns null<br>- `find()` returns same instance (Identity Map) |
| `EntityManager/FindOneByTest.phpt` | - `findOneBy()` with matching query<br>- `findOneBy()` with no match returns null<br>- `findOneBy()` with multiple matches returns first |
| `EntityManager/FindByTest.phpt` | - `findBy()` returns ElasticEntityCollection<br>- `findBy()` with pagination (limit/offset)<br>- `findBy()` with sorting<br>- `findBy()` empty result returns empty collection |
| `EntityManager/FindAllTest.phpt` | - `findAll()` returns all entities<br>- `findAll()` on empty index |
| `EntityManager/RemoveTest.phpt` | - `remove()` deletes entity<br>- `remove()` fires PRE_DELETE/POST_DELETE events<br>- `remove()` clears from Identity Map<br>- `remove()` with non-existent entity |

### 1.2 EventManager - Complete Test Suite
**File**: `src/EventManager.php`
**Current Coverage**: 0%

| Test File | Test Cases |
|-----------|------------|
| `EventManager/DispatchTest.phpt` | - Dispatch event to registered listener<br>- Dispatch to multiple listeners<br>- Dispatch with no listeners (no error)<br>- Event types: PRE_PERSIST, POST_PERSIST, POST_CREATE, POST_UPDATE, PRE_DELETE, POST_DELETE |
| `EventManager/ListenerRegistrationTest.phpt` | - Auto-discovery from DI container<br>- Listener receives correct entity and parent<br>- Listener for parent class receives child entities<br>- Listener for interface receives implementing entities |
| `EventManager/DispatchEventsTest.phpt` | - Recursive dispatch through nested entities<br>- Recursive dispatch through collections<br>- POST_CREATE only fires for new entities (ChangeSet integration)<br>- Parent parameter passed correctly through tree |

### 1.3 IdentityMap - Complete Test Suite
**File**: `src/Model/IdentityMap.php`
**Current Coverage**: 0%

| Test File | Test Cases |
|-----------|------------|
| `Model/IdentityMap/StoreTest.phpt` | - `store()` adds entity to map<br>- `store()` with different entity classes<br>- `store()` overwrites existing entry |
| `Model/IdentityMap/GetTest.phpt` | - `get()` returns stored entity<br>- `get()` returns null for missing<br>- `get()` with wrong class returns null |
| `Model/IdentityMap/HasTest.phpt` | - `has()` returns true for stored<br>- `has()` returns false for missing |
| `Model/IdentityMap/ClearTest.phpt` | - `clear()` removes all entities<br>- `clear()` specific class only |

### 1.4 ChangeSet - Complete Test Suite
**File**: `src/Model/ChangeSet.php`
**Current Coverage**: 0%

| Test File | Test Cases |
|-----------|------------|
| `Model/ChangeSetTest.phpt` | - `markExisting()` marks entity<br>- `isExisting()` returns true after mark<br>- `isExisting()` returns false for new entity<br>- Works with different object types<br>- Uses spl_object_hash correctly |

---

## Phase 2: Model Layer (Priority: High)

### 2.1 Query Operations

| Test File | Test Cases |
|-----------|------------|
| `Model/GetTest.phpt` | - Get by ID<br>- Get non-existent returns exception/null<br>- Get from specific index |
| `Model/GetByTest.phpt` | - Query with Term filter<br>- Query with Match filter<br>- Query with Range filter<br>- Query with Bool (must/should/must_not) |
| `Model/GetAllByTest.phpt` | - Multiple results with pagination<br>- Sorting options<br>- Empty results<br>- Complex nested queries |
| `Model/SearchTest.phpt` | - Full-text search<br>- Highlighting<br>- Search with filters |
| `Model/AggregateTest.phpt` | - Terms aggregation<br>- Date histogram aggregation<br>- Nested aggregations<br>- Metrics aggregations (avg, sum, min, max) |
| `Model/ScrollTest.phpt` | - Scroll through large dataset<br>- Scroll with custom batch size<br>- Clear scroll context |

### 2.2 Write Operations

| Test File | Test Cases |
|-----------|------------|
| `Model/InsertTest.phpt` | - Insert simple entity<br>- Insert with nested entities<br>- Insert with collections<br>- Insert with value objects<br>- Insert returns new ID |
| `Model/DeleteTest.phpt` | - Delete by ID<br>- Delete non-existent (no error)<br>- Delete from specific index |
| `Model/DeleteMultipleTest.phpt` | - Delete multiple by query<br>- Delete all matching<br>- Conflict handling |

### 2.3 Index Operations

| Test File | Test Cases |
|-----------|------------|
| `Model/Indices/CreateTest.phpt` | - Create with mapping<br>- Create with settings<br>- Create already existing (error handling) |
| `Model/Indices/DeleteTest.phpt` | - Delete existing index<br>- Delete non-existent (error handling) |
| `Model/Indices/ExistsTest.phpt` | - Returns true for existing<br>- Returns false for non-existing |
| `Model/Indices/GetTest.phpt` | - Get index info<br>- Get mapping<br>- Get settings |
| `Model/Indices/GetMappingTest.phpt` | - Retrieve full mapping<br>- Field types correct |
| `Model/Indices/PutMappingTest.phpt` | - Add new field to mapping<br>- Mapping conflict handling |
| `Model/Indices/PutSettingsTest.phpt` | - Update analysis settings<br>- Update replicas |
| `Model/Indices/OpenCloseTest.phpt` | - Close index<br>- Open closed index<br>- Operations on closed index fail |

### 2.4 Support Services

| Test File | Test Cases |
|-----------|------------|
| `Model/EntitySettingsLocatorTest.phpt` | - Locate settings by entity class<br>- Cache settings lookup<br>- Missing settings handling |
| `Model/VersionProviderTest.phpt` | - Get ES version<br>- Version-specific behavior |

---

## Phase 3: Factory Layer (Priority: High)

### 3.1 EntityFactory - Comprehensive Testing
**File**: `src/Factory/EntityFactory.php`
**Current Coverage**: Basic hydration only

| Test File | Test Cases |
|-----------|------------|
| `Factory/EntityFactory/BasicHydrationTest.phpt` | - Hydrate simple entity from Hit<br>- All scalar types (string, int, bool, float)<br>- Nullable properties |
| `Factory/EntityFactory/NestedEntityTest.phpt` | - Hydrate entity with nested EntityInterface<br>- Multiple levels of nesting<br>- Circular references handled |
| `Factory/EntityFactory/CollectionTest.phpt` | - Hydrate EntityCollectionInterface<br>- Hydrate ElasticEntityCollection<br>- Empty collections |
| `Factory/EntityFactory/ValueObjectTest.phpt` | - Hydrate ValueInterface properties<br>- ElasticId creation<br>- Date/DateTime handling |
| `Factory/EntityFactory/IdentityMapIntegrationTest.phpt` | - Returns cached entity from IdentityMap<br>- Stores new entity in IdentityMap<br>- ChangeSet marking |
| `Factory/EntityFactory/STITest.phpt` | - Hydrate correct child class for STI<br>- Parent reference stored correctly |

---

## Phase 4: Import System (Priority: Medium)

### 4.1 Import Core

| Test File | Test Cases |
|-----------|------------|
| `Import/RunTest.phpt` | - Full import cycle<br>- Progress tracking<br>- Error handling (Fatal, Error, Omit)<br>- AfterImport hooks called |
| `Import/SimpleRunTest.phpt` | - Simple import without locks<br>- Batch processing |
| `Import/Lock/FileLockTest.phpt` | - Acquire lock<br>- Release lock<br>- Concurrent lock detection<br>- Lock file cleanup |
| `Import/Lock/NullLockTest.phpt` | - Always acquires<br>- No file operations |

### 4.2 Import Data Handling

| Test File | Test Cases |
|-----------|------------|
| `Import/Response/SimpleResponseTest.phpt` | - Success response<br>- Error response<br>- Response data access |
| `Import/Run/OptionsTest.phpt` | - Default options<br>- Custom batch size<br>- Custom logging |

---

## Phase 5: Entity & Value Objects (Priority: Medium)

### 5.1 Value Objects

| Test File | Test Cases |
|-----------|------------|
| `Entity/Value/BoolValueTest.phpt` | - Create from true/false<br>- `value()` returns correct type |
| `Entity/Value/IntegerValueTest.phpt` | - Create from int<br>- Validation (if any) |
| `Entity/Value/StringValueTest.phpt` | - Create from string<br>- Empty string handling |
| `Entity/Value/NullValueTest.phpt` | - Represents null correctly |

### 5.2 Property Objects

| Test File | Test Cases |
|-----------|------------|
| `Entity/Property/ElasticIdTest.phpt` | - Create from string<br>- `value()` returns ID<br>- `isEmpty()` returns false |
| `Entity/Property/EmptyElasticIdTest.phpt` | - `isEmpty()` returns true<br>- `value()` behavior |
| `Entity/Property/DateTest.phpt` | - Create from string/DateTime<br>- Format output<br>- Timezone handling |
| `Entity/Property/DateTimeTest.phpt` | - Create from string/DateTime<br>- Includes time component<br>- Format output |

### 5.3 Import Validation

| Test File | Test Cases |
|-----------|------------|
| `Entity/Import/StringValueTest.phpt` | - Validate string input<br>- Reject invalid types |
| `Entity/Import/IntegerValueTest.phpt` | - Validate integer input<br>- Handle string numbers |
| `Entity/Import/BoolValueTest.phpt` | - Validate boolean<br>- Handle truthy/falsy values |
| `Entity/Import/DateValueTest.phpt` | - Validate date formats<br>- Invalid date handling |
| `Entity/Import/ArrayValueTest.phpt` | - Validate array input<br>- Nested validation |

---

## Phase 6: Console Commands (Priority: Low)

| Test File | Test Cases |
|-----------|------------|
| `Commands/CreateIndexTest.phpt` | - Creates index successfully<br>- Error on existing index<br>- Mapping applied correctly |
| `Commands/DeleteIndexTest.phpt` | - Deletes index<br>- Confirmation handling<br>- Error on non-existent |
| `Commands/InitializeIndexesTest.phpt` | - Creates all configured indexes<br>- Skips existing<br>- Reports progress |
| `Commands/DumpIndexTest.phpt` | - Exports to JSON file<br>- Handles large indexes<br>- File format correct |
| `Commands/LoadDumpTest.phpt` | - Imports from file<br>- Batch size option<br>- Progress reporting |
| `Commands/AddAliasTest.phpt` | - Adds alias to index<br>- Multiple aliases |
| `Commands/RemoveAliasTest.phpt` | - Removes alias<br>- Error on non-existent |

---

## Phase 7: Mapping Attributes (Priority: Low)

| Test File | Test Cases |
|-----------|------------|
| `Mapping/EntityAttributeTest.phpt` | - Attribute applied to class<br>- Reflection reads correctly |
| `Mapping/CollectionAttributeTest.phpt` | - Collection type detected<br>- Item class specified |
| `Mapping/ElasticCollectionAttributeTest.phpt` | - ElasticEntity collection detected |
| `Mapping/IgnoredAttributeTest.phpt` | - Property excluded from serialization |
| `Mapping/STIEntityAttributeTest.phpt` | - STI parent identified<br>- Child classes resolved |
| `Mapping/STIElasticEntityAttributeTest.phpt` | - STI elastic entity handling |

---

## Phase 8: Error Handling & Edge Cases (Priority: Medium)

| Test File | Test Cases |
|-----------|------------|
| `Exception/DocumentNotFoundTest.phpt` | - Thrown when document missing<br>- Contains ID in message |
| `Exception/InvalidPropertyPathTest.phpt` | - Thrown on bad property access<br>- Contains path info |
| `Exception/ElasticSearchExceptionTest.phpt` | - Wraps ES client exceptions<br>- Preserves error details |
| `EdgeCases/EmptyIndexTest.phpt` | - All operations on empty index<br>- No errors, correct empty results |
| `EdgeCases/LargeDocumentTest.phpt` | - Documents near size limit<br>- Deep nesting |
| `EdgeCases/SpecialCharactersTest.phpt` | - Unicode in values<br>- Special ES characters escaped |
| `EdgeCases/ConcurrencyTest.phpt` | - Version conflicts<br>- Optimistic locking |

---

## Test Infrastructure Requirements

### New Test Entities Needed

```
tests/SpameriTests/Elastic/Data/Entity/
├── SimpleEntity.php          # Minimal entity for basic tests
├── ComplexEntity.php         # Many nested levels
├── STIParentEntity.php       # STI parent
├── STIChildEntity.php        # STI child
├── EntityWithAllTypes.php    # All supported property types
└── CircularA.php / B.php     # Circular reference testing
```

### Test Configuration Updates

```neon
# tests/SpameriTests/Elastic/Data/Config/Common.neon additions
services:
    - SpameriTests\Elastic\Data\Listener\TestListener  # For event testing
    - SpameriTests\Elastic\Data\Import\TestDataProvider
```

### Helper Classes

```
tests/SpameriTests/Elastic/
├── Helper/
│   ├── IndexHelper.php       # Create/delete test indexes
│   ├── DataHelper.php        # Generate test data
│   └── AssertHelper.php      # Custom assertions
└── Mock/
    ├── MockListener.php      # Tracks event calls
    └── MockDataProvider.php  # Controllable data source
```

---

## Implementation Priority Order

1. **Week 1-2**: Phase 1 (Core Infrastructure)
   - EntityManager complete coverage
   - EventManager complete coverage
   - IdentityMap tests
   - ChangeSet tests

2. **Week 3-4**: Phase 2 & 3 (Model & Factory)
   - All query operations
   - All write operations
   - EntityFactory comprehensive tests

3. **Week 5**: Phase 4 & 5 (Import & Value Objects)
   - Import system tests
   - Value object tests

4. **Week 6**: Phase 6, 7, 8 (Commands, Mapping, Edge Cases)
   - Console command tests
   - Mapping attribute tests
   - Edge case coverage

---

## Test Execution Strategy

### Unit Tests (No ElasticSearch Required)
- ChangeSet
- Value objects
- Import validators
- Mapping attributes
- Exception classes

### Integration Tests (Require ElasticSearch)
- EntityManager operations
- Model layer operations
- EntityFactory with real data
- Import system
- Console commands

### Running Tests

```bash
# All tests
make tests

# Unit tests only (fast, no ES)
vendor/bin/tester -s tests/SpameriTests/Elastic/Unit/

# Integration tests (requires ES)
vendor/bin/tester -s tests/SpameriTests/Elastic/Integration/

# Single component
vendor/bin/tester -s tests/SpameriTests/Elastic/EntityManager/

# With coverage
make coverage
```

---

## Success Metrics

| Metric | Target |
|--------|--------|
| Line Coverage | 80%+ |
| Branch Coverage | 70%+ |
| Mutation Score | 60%+ |
| Test Files | 60-70 files |
| Test Cases | 200+ assertions |
| CI Pipeline | All green |

---

## Notes

- All tests follow existing patterns from `AbstractTestCase`
- Use `.phpt` format (Nette Tester)
- Each test creates/deletes its own indexes (isolation)
- Prefer real ES integration over mocking for accuracy
- Document any ES version-specific behavior
