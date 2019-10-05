<?php
namespace App\Database;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
class UpsertBuilder
{
    /** @var Builder */
    protected $builder;
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }
    /**
     * @param Collection $values
     *
     * @return Collection
     */
    protected function getWrappedColumns(Collection $values): Collection
    {
        return collect($values->first())->keys()->map([$this->builder->grammar, 'wrap']);
    }
    /**
     * @param Collection $values
     *
     * @return Collection
     */
    protected function getQuotedValues(Collection $values): Collection
    {
        return $values->map(function ($row) {
            return collect($row)->map(function ($value) {
                if (is_null($value)) {
                    return 'NULL';
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                return $this->builder->connection->getPdo()->quote($value);
            });
        });
    }
    /**
     * @param Collection $values
     *
     * @return string
     */
    protected function generateInsertValues(Collection $values): string
    {
        return $values->map(function (Collection $row) {
            return '(' . $row->implode(',') . ')';
        })->implode(',');
    }
    /**
     * @param Collection $columns
     *
     * @return string
     */
    protected function generateUpdateValues(Collection $columns): string
    {
        return $columns->map(function ($column) {
            return sprintf(
                '%s=%s',
                $column,
                "excluded.${column}"
            );
        })->implode(',');
    }
    /**
     * @param array $values
     *
     * @return string
     */
    public function getQuery(array $values, $conflict): string
    {
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $values = collect($values);
        $wrappedColumns = $this->getWrappedColumns($values);

        return sprintf(
            'INSERT INTO %s (%s) VALUES %s ON CONFLICT %s DO UPDATE SET %s',
            $this->builder->grammar->wrapTable($this->builder->from),
            $wrappedColumns->implode(','),
            $this->generateInsertValues($this->getQuotedValues($values)),
            $conflict,
            $this->generateUpdateValues($wrappedColumns)
        );
    }
}