<?php

namespace App\Traits;

trait DatatableFilter
{
    public function buildRawFilters($filters)
    {
        $rawFilters = [];
        foreach ($filters as $filter) {
            $rawFilters[] = "{$filter["column"]} {$filter["operator"]} {$filter["value"]}";
        }

        return $rawFilters ? implode(" AND ", $rawFilters) : null;
    }

    public function filterValue($column, $operator, $value)
    {
        return [
            "column" => $column,
            "operator" => $operator,
            "value" => $value,
        ];
    }
}
?>