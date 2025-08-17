# Mongo SQL Where

Convert MongoDB-style query syntax into SQL WHERE clauses in PHP.

## Features

- Supports `$or`, `$and`, `$not`, `$in`, `$nin`, `$gt`, `$lt`, `$gte`, `$lte`, `$ne`
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

## Output

```sql
WHERE (users.status = 'active' OR products.qty < 50) AND NOT (products.category IN ('banned', 'restricted'))
```

## License

MIT
