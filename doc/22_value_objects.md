# Value Objects Guide

## Overview

Value objects are immutable objects that represent a value with validation logic. They implement `ValueInterface` and are used to ensure data integrity at the entity level.

## Why Use Value Objects?

### Without Value Objects (Primitive Obsession)

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public string $title,      // Any string, including empty
        public string $email,      // No email validation
        public int $duration,      // Could be negative
        public string $imdbId,     // No format validation
    ) {
        parent::__construct($id);
    }
}

// Problems:
$video = new Video(
    new EmptyElasticId(),
    '',                    // Empty title - valid?
    'not-an-email',       // Invalid email - accepted
    -100,                  // Negative duration - makes no sense
    'invalid',             // Wrong IMDB format - goes to database
);
```

### With Value Objects

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public Title $title,       // Validated non-empty string
        public Email $email,       // Validated email format
        public Duration $duration, // Validated positive integer
        public ImdbId $imdbId,     // Validated IMDB format
    ) {
        parent::__construct($id);
    }
}

// Invalid data throws immediately:
$video = new Video(
    new EmptyElasticId(),
    new Title(''),         // throws InvalidArgumentException
    new Email('invalid'),  // throws InvalidArgumentException
    new Duration(-100),    // throws InvalidArgumentException
    new ImdbId('invalid'), // throws InvalidArgumentException
);
```

## The ValueInterface

```php
interface ValueInterface
{
    public function value(): mixed;
}
```

The interface is minimal - just one method that returns the underlying value.

## Basic Value Object

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class Title implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private string $value,
    ) {
        if (\trim($value) === '') {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

## Common Value Object Patterns

### Email

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class Email implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private string $value,
    ) {
        if (!\filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                'Invalid email format: ' . $value
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return \explode('@', $this->value)[1];
    }
}
```

### Positive Integer

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class PositiveInt implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private int $value,
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException(
                'Value must be positive, got: ' . $value
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
```

### Bounded Integer (Rating)

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class Rating implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private int $value,
    ) {
        if ($value < 1 || $value > 10) {
            throw new \InvalidArgumentException(
                'Rating must be between 1 and 10, got: ' . $value
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function isExcellent(): bool
    {
        return $this->value >= 8;
    }
}
```

### URL

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class Url implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private string $value,
    ) {
        if (!\filter_var($value, \FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(
                'Invalid URL: ' . $value
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function host(): string
    {
        return \parse_url($this->value, \PHP_URL_HOST) ?? '';
    }
}
```

### External ID (IMDB)

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class ImdbId implements \Spameri\Elastic\Entity\ValueInterface
{
    private const PATTERN = '/^tt\d{7,}$/';

    public function __construct(
        private string $value,
    ) {
        if (!\preg_match(self::PATTERN, $value)) {
            throw new \InvalidArgumentException(
                'Invalid IMDB ID format: ' . $value . ' (expected tt followed by 7+ digits)'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function url(): string
    {
        return 'https://www.imdb.com/title/' . $this->value;
    }
}
```

### Slug

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity\Property;

readonly class Slug implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private string $value,
    ) {
        if (!\preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new \InvalidArgumentException(
                'Invalid slug format: ' . $value
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromString(string $input): self
    {
        $slug = \strtolower($input);
        $slug = \preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = \trim($slug, '-');

        return new self($slug);
    }
}
```

## Built-in Value Objects

The library provides some value objects out of the box:

### ElasticId

```php
readonly class ElasticId implements ValueInterface, ElasticIdInterface
{
    public const FIELD_NAME = '_id';

    public function __construct(
        private string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException();
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

### Date

```php
class Date extends \Nette\Utils\DateTime implements DateTimeInterface
{
    public const FORMAT = 'Y-m-d';

    public function format(string $format = ''): string
    {
        if ($format === '') {
            $format = self::FORMAT;
        }
        return parent::format($format);
    }
}
```

### DateTime

```php
class DateTime extends \Nette\Utils\DateTime implements DateTimeInterface
{
    public const FORMAT = 'Y-m-d\TH:i:s';

    public function format(string $format = ''): string
    {
        if ($format === '') {
            $format = self::FORMAT;
        }
        return parent::format($format);
    }

    public function formatTimeAgo(): string
    {
        // Human-readable time difference
    }
}
```

## Nullable Value Objects

For optional fields, use nullable types:

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public Title $title,           // Required
        public ?Description $description, // Optional
    ) {
        parent::__construct($id);
    }
}
```

## Value Object Collections

For lists of value objects, use typed arrays directly:

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public Title $title,
        /** @var array<Tag> */
        public array $tags = [],
    ) {
        parent::__construct($id);
    }

    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }

    public function hasTag(Tag $tag): bool
    {
        foreach ($this->tags as $existingTag) {
            if ($existingTag->value() === $tag->value()) {
                return true;
            }
        }
        return false;
    }
}

// Creating
$video = new Video(
    new EmptyElasticId(),
    new Title('Die Hard'),
    [new Tag('action'), new Tag('thriller')],
);

// Adding tags
$video->addTag(new Tag('classic'));
```

The library automatically serializes arrays of `ValueInterface` objects to their primitive values.

## Equality Comparison

Value objects should be compared by value, not reference:

```php
$email1 = new Email('test@example.com');
$email2 = new Email('test@example.com');

$email1 === $email2; // false (different objects)
$email1->value() === $email2->value(); // true (same value)
```

For proper equality, add an `equals()` method:

```php
readonly class Email implements ValueInterface
{
    // ...

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## Transforming Value Objects

Sometimes you need to create derived values:

```php
readonly class Price implements ValueInterface
{
    public function __construct(
        private float $value,
        private string $currency = 'USD',
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function withDiscount(float $percentage): self
    {
        $discounted = $this->value * (1 - $percentage / 100);
        return new self($discounted, $this->currency);
    }

    public function format(): string
    {
        return \number_format($this->value, 2) . ' ' . $this->currency;
    }
}
```

## Best Practices

### 1. Keep Value Objects Immutable

```php
// Good: readonly class
readonly class Email implements ValueInterface
{
    public function __construct(private string $value) {}
}

// Bad: mutable property
class Email implements ValueInterface
{
    public function __construct(public string $value) {} // Can be modified!
}
```

### 2. Validate in Constructor

```php
// Good: Early validation
public function __construct(private string $value)
{
    if (!$this->isValid($value)) {
        throw new \InvalidArgumentException('Invalid value');
    }
}

// Bad: Deferred validation
public function validate(): bool
{
    return $this->isValid($this->value);
}
```

### 3. Be Specific with Exceptions

```php
// Good: Specific error message
throw new \InvalidArgumentException(
    \sprintf('Email must be valid format, got "%s"', $value)
);

// Bad: Generic message
throw new \InvalidArgumentException('Invalid');
```

### 4. Use Named Constructors for Complex Creation

```php
readonly class DateRange implements ValueInterface
{
    private function __construct(
        private \DateTimeInterface $start,
        private \DateTimeInterface $end,
    ) {
        if ($start > $end) {
            throw new \InvalidArgumentException('Start must be before end');
        }
    }

    public static function create(
        \DateTimeInterface $start,
        \DateTimeInterface $end,
    ): self {
        return new self($start, $end);
    }

    public static function fromToday(int $days): self
    {
        $start = new \DateTime();
        $end = (new \DateTime())->modify("+{$days} days");
        return new self($start, $end);
    }

    public function value(): array
    {
        return ['start' => $this->start, 'end' => $this->end];
    }
}
```

### 5. Document Expected Formats

```php
/**
 * IMDB ID value object.
 *
 * Expected format: tt followed by 7 or more digits (e.g., tt0111161)
 *
 * @see https://www.imdb.com/interfaces/
 */
readonly class ImdbId implements ValueInterface
{
    // ...
}
```

## ElasticSearch Mapping

Value objects are stored as their primitive values in ElasticSearch:

```php
// Entity with value objects
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public Title $title,
        public Rating $rating,
        public ImdbId $imdbId,
    ) {
        parent::__construct($id);
    }
}

// Stored in ElasticSearch as:
{
    "_id": "abc123",
    "_source": {
        "title": "The Shawshank Redemption",
        "rating": 9,
        "imdbId": "tt0111161"
    }
}
```

The mapping should match the underlying type:

```php
$settings->addMappingField(new Field('title', TYPE_TEXT));
$settings->addMappingField(new Field('rating', TYPE_INTEGER));
$settings->addMappingField(new Field('imdbId', TYPE_KEYWORD));
```

## Next Steps

- [Data Interfaces](04_data_interfaces.md) - Interface hierarchy
- [Entity Class Guide](03_entity_class.md) - Using value objects in entities
- [Mapping Attributes](18_mapping_attributes.md) - Property annotations
