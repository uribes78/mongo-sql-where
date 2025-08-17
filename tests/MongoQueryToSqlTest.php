<?php

use PHPUnit\Framework\TestCase;
use Tico\MongoSqlWhere\MongoQueryToSql;

class MongoQueryToSqlTest extends TestCase {
    public function testSimpleEquality() {
        $query = ['status' => 'active'];
        $map = ['status' => 'users.status'];
        $converter = new MongoQueryToSql($map);
        $sql = $converter->convert($query);
        $this->assertEquals("WHERE users.status = 'active'", $sql);
    }

    public function testComplexQuery() {
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
        $sql = $converter->convert($query);
        $expected = "WHERE ((users.status = 'active') OR (products.qty < 50)) AND NOT (products.category IN ('banned', 'restricted'))";
        $this->assertEquals($expected, $sql);
    }

    public function testRegexOperator() {
        // Simple regex
        $query = ['name' => ['$regex' => '^test']];
        $converter = new MongoQueryToSql();
        $sql = $converter->convert($query);
        $this->assertEquals("WHERE name REGEXP '^test'", $sql);

        // Case insensitive regex
        $query = ['name' => [
            '$regex' => 'test',
            '$options' => 'i'
        ]];
        $sql = $converter->convert($query);
        $this->assertEquals("WHERE LOWER(name) REGEXP LOWER('test')", $sql);

        // With column mapping
        $query = ['username' => ['$regex' => '^user_']];
        $map = ['username' => 'users.username'];
        $converter = new MongoQueryToSql($map);
        $sql = $converter->convert($query);
        $this->assertEquals("WHERE users.username REGEXP '^user_'", $sql);

        // With special characters
        $query = ['email' => ['$regex' => '^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$']];
        $sql = $converter->convert($query);
        $this->assertEquals("WHERE email REGEXP '^[a-z0-9._%+-]+@[a-z0-9.-]+\\\\.[a-z]{2,}$'", $sql);
    }

    public function testRegexInComplexQuery() {
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
        $sql = $converter->convert($query);
        $expected = "WHERE users.status = 'active' AND ((users.name REGEXP '^A') OR (LOWER(users.email) REGEXP LOWER('@example\\\.com$')))";
        $this->assertEquals($expected, $sql);
    }
}

