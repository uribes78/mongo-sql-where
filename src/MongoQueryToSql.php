<?php

namespace Tico\MongoSqlWhere;

class MongoQueryToSql {
    private array $columnMap;

    public function __construct(array $columnMap = [])
    {
        $this->columnMap = $columnMap;
    }

    public function convert(array $query): string
    {
        $clauses = $this->parse($query);
        return 'WHERE ' . implode(' AND ', $clauses);
    }

    private function parse(array $query): array
    {
        $sql = [];

        foreach ($query as $key => $value) {
            if ($key === '$or') {
                $groups = array_map([$this, 'parse'], $value);
                $sql[] = '(' . implode(' OR ', array_map(fn($g) => '(' . implode(' AND ', $g) . ')', $groups)) . ')';
            } elseif ($key === '$and') {
                $groups = array_map([$this, 'parse'], $value);
                $sql[] = '(' . implode(' AND ', array_map(fn($g) => '(' . implode(' AND ', $g) . ')', $groups)) . ')';
            } elseif ($key === '$not') {
                $notClauses = $this->parse($value);
                $sql[] = 'NOT (' . implode(' AND ', $notClauses) . ')';
            } elseif (is_array($value)) {
                foreach ($value as $op => $val) {
                    $column = $this->mapColumn($key);
                    $sql[] = match ($op) {
                        '$gt'  => "$column > " . $this->quote($val),
                        '$gte' => "$column >= " . $this->quote($val),
                        '$lt'  => "$column < " . $this->quote($val),
                        '$lte' => "$column <= " . $this->quote($val),
                        '$ne'  => "$column != " . $this->quote($val),
                        '$in'  => "$column IN (" . implode(', ', array_map([$this, 'quote'], $val)) . ")",
                        '$nin' => "$column NOT IN (" . implode(', ', array_map([$this, 'quote'], $val)) . ")",
                        default => "$column = " . $this->quote($val)
                    };
                }
            } else {
                $column = $this->mapColumn($key);
                $sql[] = "$column = " . $this->quote($value);
            }
        }

        return $sql;
    }

    private function mapColumn(string $key): string
    {
        return $this->columnMap[$key] ?? $key;
    }

    private function quote($val): string
    {
        return is_numeric($val) ? $val : "'" . addslashes($val) . "'";
    }
}
?>
