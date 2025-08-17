# Mongo SQL Where

Convert MongoDB-style query syntax into SQL WHERE clauses in PHP.

## Features

- Supports `$or`, `$and`, `$not`, `$in`, `$nin`, `$gt`, `$lt`, `$gte`, `$lte`, `$ne`, `$regex`
- Column mapping for aliasing logical keys to SQL columns
- No ORM required

## Installation

```bash
composer require tico/mongo-sql-where
```
## Usage

```php
use Tico\MongoSqlWhere\MongoQueryToSql;

$query = [
    '$or' => [
        ['status' => 'active'],
        ['qty' => ['$lt' => 50]]
    ],
    '$not' => ['category' => ['$in' => ['banned', 'restricted']]]
];

$map = [
    'status' => 'users.status',
    'qty' => 'products.qty',
    'category' => 'products.category'
];

$converter = new MongoQueryToSql($map);
echo $converter->convert($query);
```

## Example Output

```sql
WHERE (users.status = 'active' OR products.qty < 50) AND NOT (products.category IN ('banned', 'restricted'))
```

## Regex Support

You can use the `$regex` operator to perform pattern matching. Case-insensitive matching is supported via the `$options` parameter.

```php
// Simple regex
$query = ['name' => ['$regex' => '^test']];
// Converts to: WHERE name REGEXP '^test'

// Case-insensitive regex
$query = [
    'email' => [
        '$regex' => '@example\\.com$',
        '$options' => 'i'  // 'i' for case-insensitive
    ]
];
// Converts to: WHERE LOWER(email) REGEXP LOWER('@example\\.com$')

// In complex queries
$query = [
    'status' => 'active',
    '$or' => [
        ['name' => ['$regex' => '^A']],
        ['email' => ['$regex' => '@example\\.com$', '$options' => 'i']]
    ]
];
$map = [
    'status' => 'users.status',
    'name' => 'users.name',
    'email' => 'users.email'
];

$converter = new MongoQueryToSql($map);
echo $converter->convert($query);
```

## Example Output

```sql
WHERE (users.status = 'active' OR users.name REGEXP '^A') AND (LOWER(users.email) REGEXP LOWER('@example\\\.com$'))
```

## License

MIT
