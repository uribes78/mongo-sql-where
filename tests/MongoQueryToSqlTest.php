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
}

