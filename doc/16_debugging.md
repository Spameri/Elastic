# Tracy Debugging Guide

## Overview

Spameri/Elastic includes integrated support for [Tracy](https://tracy.nette.org/) - the popular PHP debugging tool. The integration provides a Tracy Bar panel that displays all ElasticSearch queries executed during a request, including:

- Query URIs (endpoints called)
- Request bodies (full query JSON)
- Response bodies (full response JSON)
- Query duration (from ElasticSearch's `took` field)
- Total query time and count

This makes debugging and optimizing ElasticSearch queries significantly easier.

---

## Enabling Tracy Integration

### 1. Enable Debug Mode

In your neon configuration, set `debug: true`:

```neon
spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
    debug: true  # ← Enables Tracy panel
    version: 8
```

**Important:** Only enable in development/staging environments. Disable in production for performance.

---

### 2. Ensure Tracy is Installed

The integration requires Tracy and the elastic/transport package:

```bash
composer require tracy/tracy
```

These are typically already installed as dependencies.

---

### 3. Verify Panel Appears

After enabling debug mode:

1. Load any page that makes ElasticSearch queries
2. Look for the Tracy Bar (usually bottom-right corner)
3. Find the "Elastic" panel showing query count and total time

**Example Tracy Bar Tab:**
```
Elastic: 5 queries / 42.3ms
```

---

## Understanding the Panel

### Tab Information

The Tracy Bar tab shows a quick summary:

- **Query Count** - Total number of ElasticSearch requests
- **Total Duration** - Sum of all query `took` times (in milliseconds)

**Example:**
```
Elastic: 12 queries / 156.7ms
```

This means 12 requests were made to ElasticSearch, taking a total of 156.7ms (as reported by ElasticSearch itself, not including network overhead).

---

### Panel Content

Click the panel tab to expand the full panel. It displays a table with columns:

| Column | Description |
|--------|-------------|
| **Uri** | ElasticSearch endpoint (e.g., `/videos/_search`, `/videos/_doc/abc123`) |
| **Took** | Duration in milliseconds (from ES response) |
| **Request** | Full request body (collapsible JSON) |
| **Request string** | Raw JSON string (for copy-paste) |
| **Response** | Full response body (collapsible JSON) |

---

### Request Column

Shows the JSON payload sent to ElasticSearch. Click "Show" to expand.

**Example Request:**
```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "term": {
                        "status": "published"
                    }
                }
            ]
        }
    },
    "size": 10,
    "from": 0
}
```

The JSON is rendered with Tracy's dump tool, providing:
- Syntax highlighting
- Expandable/collapsible sections
- Deep nesting support (up to 30 levels)

---

### Request String Column

Shows the same request as a raw JSON string. Useful for:
- Copy-pasting into ElasticSearch tools (Kibana, curl, etc.)
- Debugging JSON formatting issues
- Sharing queries with team members

---

### Response Column

Shows the full ElasticSearch response. Click "Show" to expand.

**Example Response:**
```json
{
    "took": 12,
    "timed_out": false,
    "_shards": {
        "total": 1,
        "successful": 1,
        "skipped": 0,
        "failed": 0
    },
    "hits": {
        "total": {
            "value": 42,
            "relation": "eq"
        },
        "max_score": 1.0,
        "hits": [...]
    }
}
```

Includes:
- Timing information
- Shard status
- Hit count and scores
- Full document sources
- Aggregation results (if any)

---

## Common Debugging Workflows

### 1. Finding Slow Queries

**Problem:** Application feels slow.

**Solution:**

1. Enable Tracy panel
2. Navigate through your application
3. Check Tracy panel for query times
4. Sort mentally by the "Took" column
5. Identify queries over 100ms

**Optimization Strategy:**
- Add indexes for filtered fields
- Reduce result size
- Use aggregations instead of fetching all data
- Consider scroll API for large result sets

**Example:**
```
Uri: /videos/_search
Took: 543ms  ← Too slow!

Request shows:
{
    "query": {
        "match": {
            "description": "very long text..."  ← Full-text search without optimization
        }
    },
    "size": 1000  ← Fetching too much
}
```

**Fix:**
- Reduce `size` to 10-20
- Add specific filters to narrow results
- Consider pagination

---

### 2. Debugging Missing Results

**Problem:** Expected documents don't appear in results.

**Solution:**

1. Find the query in Tracy panel
2. Expand the Request to see exact query sent
3. Expand the Response to see what ES returned
4. Check `hits.total.value` - is it 0?
5. Verify query structure matches your mapping

**Example Issue:**
```json
Request:
{
    "query": {
        "term": {
            "status": "Published"  ← Case-sensitive!
        }
    }
}

Response:
{
    "hits": {
        "total": {"value": 0}  ← No results
    }
}
```

**Problem:** `term` queries are case-sensitive and exact match. The field contains `"published"` (lowercase).

**Fix:** Use `match` query or lowercase the search term.

---

### 3. Verifying Query Structure

**Problem:** Unsure if query is constructed correctly.

**Solution:**

1. Execute the operation
2. Check Tracy panel Request column
3. Compare against ElasticSearch documentation
4. Copy Request string and test in Kibana/curl if needed

**Example:**
```php
// In code
$query = new \Spameri\ElasticQuery\ElasticQuery();
$query->query()->must()->add(
    new \Spameri\ElasticQuery\Query\Range('year', 2020, 2025)
);
```

**Tracy shows actual JSON sent:**
```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "range": {
                        "year": {
                            "gte": 2020,
                            "lte": 2025
                        }
                    }
                }
            ]
        }
    }
}
```

Confirms the query builder produced correct structure.

---

### 4. Analyzing Aggregation Results

**Problem:** Aggregation returns unexpected data.

**Solution:**

1. Find aggregation query in Tracy panel
2. Expand Response
3. Navigate to `aggregations` section
4. Verify bucket counts and values

**Example:**
```json
Request:
{
    "aggregations": {
        "videos_by_year": {
            "terms": {
                "field": "year",
                "size": 10
            }
        }
    }
}

Response:
{
    "aggregations": {
        "videos_by_year": {
            "buckets": [
                {"key": 2024, "doc_count": 150},
                {"key": 2023, "doc_count": 98},
                {"key": 2022, "doc_count": 67}
            ]
        }
    }
}
```

You can immediately see distribution and verify correctness.

---

### 5. Debugging Bulk Operations

**Problem:** Bulk insert/update fails partially.

**Solution:**

1. Find bulk operation in Tracy (URI will be `/_bulk`)
2. Expand Response
3. Check `errors: true/false`
4. If true, inspect `items` array for failed operations

**Example Response:**
```json
{
    "took": 45,
    "errors": true,  ← Some operations failed
    "items": [
        {
            "index": {
                "_id": "1",
                "status": 201  ← Success
            }
        },
        {
            "index": {
                "_id": "2",
                "status": 400,  ← Failed
                "error": {
                    "type": "mapper_parsing_exception",
                    "reason": "failed to parse field [year] of type [long]"
                }
            }
        }
    ]
}
```

Immediately identifies which document failed and why (invalid year value).

---

## Configuration

### Debug Mode Toggle

```neon
# Development
spameriElasticSearch:
    debug: true

# Production
spameriElasticSearch:
    debug: false
```

When `debug: false`:
- Tracy panel is NOT registered
- No query logging overhead
- PanelLogger is NOT used

---

### Custom Logger Integration

By default, `PanelLogger` wraps your existing logger. Queries are logged to both Tracy and your configured logger.

**How It Works:**

1. DI extension checks `debug` configuration
2. If true, creates `PanelLogger` wrapping the default logger
3. `PanelLogger` intercepts all log calls
4. Extracts query info from context (request/response objects)
5. Stores for Tracy panel display
6. Forwards to wrapped logger for normal logging

**Result:** You get Tracy panel without losing your file/database logs.

---

## Performance Considerations

### Development Impact

**With debug: true**
- Each query logged to memory
- Request/response bodies stored
- JSON parsing for display
- Tracy panel rendering

**Overhead:** ~5-10ms per request (depends on query count)

**Recommendation:** Always enable in development.

---

### Production Impact

**With debug: false**
- Zero Tracy overhead
- No query logging
- No JSON parsing
- No panel rendering

**Recommendation:** ALWAYS disable in production.

---

### Memory Usage

Each logged query stores:
- Request body (JSON string + parsed array)
- Response body (JSON string + parsed array)

**Typical:** 5-50 KB per query

**Many Queries:** With 100+ queries, memory usage can reach several MB.

**Mitigation:** Reduce query count via:
- Caching
- Query optimization
- Batch operations

---

## Advanced Usage

### Accessing Query Log Programmatically

The `PanelLogger` stores queries and can be accessed from DI:

```php
class DebugController
{
    public function __construct(
        private \Spameri\Elastic\Diagnostics\PanelLogger $logger
    ) {}

    public function debugAction(): void
    {
        $queries = $this->logger->getQueries();
        $requests = $this->logger->getRequestBodies();
        $responses = $this->logger->getResponseBodies();

        // Analyze, export, or process queries programmatically
    }
}
```

**Use Cases:**
- Custom reporting
- Query analysis tools
- Performance profiling
- Automated testing

---

### Testing Query Structure

In tests, you can verify exact queries sent:

```php
public function testSearchQuery(): void
{
    $query = new \Spameri\ElasticQuery\ElasticQuery();
    $query->query()->must()->add(new \Spameri\ElasticQuery\Query\Match('title', 'keyword'));
    $result = $this->entityManager->findBy($query, \App\Entity\Video::class);

    $queries = $this->panelLogger->getQueries();
    $lastQuery = end($queries);

    Assert::contains('/_search', $lastQuery['uri']);
    Assert::contains('"match"', $lastQuery['requestBodyString']);
}
```

---

## Troubleshooting

### Panel Doesn't Appear

**Possible Causes:**

1. **debug: false in config**
   - Check neon file
   - Verify you're in development mode

2. **No queries executed**
   - Panel only appears if queries were made
   - Try operation that uses ElasticSearch

3. **Tracy not properly initialized**
   - Ensure Tracy is enabled in bootstrap
   - Check Tracy bar appears for other panels

4. **DI cache outdated**
   - Clear temp/cache directory
   - Refresh page

---

### Panel Shows No Queries

**Cause:** Queries executed before panel initialization or through different client instance.

**Solution:** Ensure all ES operations go through injected services, not directly constructed clients.

---

### Request/Response Shows "Show" But Won't Expand

**Cause:** JavaScript issue or malformed JSON.

**Solution:**
1. Check browser console for JS errors
2. Use Request string column (always works)
3. Verify response is valid JSON

---

### Duration Shows "null"

**Cause:** Response didn't include `took` field (not a search/query operation).

**Examples:**
- Index creation
- Mapping updates
- Admin operations

These operations don't report timing in the same way.

---

## Best Practices

### 1. Always Enable in Development

```neon
# config.local.neon (not in git)
spameriElasticSearch:
    debug: true
```

Benefits:
- Understand query behavior
- Catch inefficient queries early
- Verify query correctness
- Learn ElasticSearch query structure

---

### 2. Review Panel Before Committing

Before committing code that touches ElasticSearch:

1. Execute relevant operations
2. Check Tracy panel
3. Verify query count is reasonable
4. Check for slow queries (>100ms)
5. Ensure query structure is optimal

---

### 3. Use Request String for Documentation

When documenting API behavior or reporting issues:

1. Copy Request string from Tracy
2. Include in documentation/issue
3. Others can reproduce exact query

**Example:**
```
When searching for videos by year, the following query is sent:

{
    "query": {
        "range": {
            "year": {"gte": 2020, "lte": 2025}
        }
    }
}
```

---

### 4. Monitor Query Count

Keep an eye on query count per page:

- **1-5 queries** - Good
- **6-15 queries** - Acceptable
- **16-30 queries** - Consider optimization
- **30+ queries** - Definitely optimize!

Common issues:
- N+1 queries (fetching relations in loop)
- Missing eager loading
- Redundant queries

---

### 5. Copy Queries to Kibana for Testing

Tracy makes it easy to test in Kibana:

1. Copy Request string from Tracy
2. Open Kibana Dev Tools
3. Paste query
4. Modify and test variations
5. Copy working query back to code

---

## Integration with Other Tools

### Kibana Integration

Tracy panel complements Kibana:

**Tracy:** See queries your app actually sends
**Kibana:** Test and refine queries

**Workflow:**
1. See query in Tracy
2. Copy to Kibana
3. Experiment with variations
4. Update application code
5. Verify in Tracy

---

### xdebug Integration

Use Tracy with xdebug for complete debugging:

1. Set breakpoint in service
2. Execute operation
3. Step through code
4. Check Tracy panel for generated query
5. Verify query matches expectations

---

### Log File Correlation

Since PanelLogger forwards to your regular logger:

1. Query appears in Tracy
2. Same query logged to file
3. Correlate Tracy visual with log entry
4. Useful for production issue debugging

---

## Summary

The Tracy integration provides:

✅ Real-time query visibility
✅ Request/response inspection
✅ Performance monitoring
✅ Query structure verification
✅ Zero production overhead (when disabled)
✅ Integration with existing logging

**Enable it in development** for significantly improved debugging experience when working with ElasticSearch.

---

## See Also

- [Model Services](14_model_services.md) - Services that generate queries
- [Basic Get](08_basic_get.md) - Simple queries
- [Match Get](09_match_get.md) - Search queries
- [Advanced Get](13_advanced_get.md) - Complex queries
- [Aggregate](10_aggregate.md) - Aggregation queries
