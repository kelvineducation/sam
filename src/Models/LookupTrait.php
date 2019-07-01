<?php

namespace App\Models;

trait LookupTrait
{
    public static function findByLookup($lookup_value)
    {
        $column = self::db()->quoteCol(self::$lookup_column);

        return self::findWhere(
            "{$column} = $1",
            [$lookup_value]
        );
    }
}
