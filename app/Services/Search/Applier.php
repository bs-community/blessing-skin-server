<?php

namespace App\Services\Search;

use Illuminate\Database\Eloquent\Builder;

/**
 * Applies a parsed search tree to an Eloquent builder.
 *
 * Column behaviour is driven by the model's $searchable definition:
 *
 *   'uid'                                 plain column, exact match
 *   'email'        => ['searchable' => true]   also matched by free-text terms
 *   'register_at'  => ['date' => true]         bare date matches the whole day
 *   'verified'     => ['boolean' => true]      true/false/yes/no/1/0
 *   'tid_skin'     => ['alias' => ['skin']]    alternative names for the column
 */
class Applier
{
    /** @var array<string, array> column => options */
    private array $columns = [];

    /** @var array<string, string> alias => column */
    private array $aliases = [];

    /** @var array<int, string> */
    private array $searchable = [];

    public function __construct(array $definition)
    {
        foreach ($definition as $key => $value) {
            $column = is_int($key) ? $value : $key;
            $options = is_int($key) ? [] : (array) $value;

            $this->columns[$column] = $options;
            $this->aliases[$column] = $column;

            foreach ((array) ($options['alias'] ?? []) as $alias) {
                $this->aliases[$alias] = $column;
            }

            if ($options['searchable'] ?? false) {
                $this->searchable[] = $column;
            }
        }
    }

    public function apply(Builder $query, ?array $node): Builder
    {
        if ($node === null) {
            return $query;
        }

        $this->applyNode($query, $node);

        return $query;
    }

    private function applyNode(Builder $query, array $node, string $boolean = 'and'): void
    {
        match ($node['type']) {
            'and', 'or' => $query->where(function (Builder $nested) use ($node): void {
                foreach ($node['nodes'] as $child) {
                    $this->applyNode($nested, $child, $node['type']);
                }
            }, null, null, $boolean),
            'not' => $query->whereNot(function (Builder $nested) use ($node): void {
                $this->applyNode($nested, $node['node']);
            }, null, null, $boolean),
            'constraint' => $this->applyConstraint($query, $node, $boolean),
            'in' => $this->applyIn($query, $node, $boolean),
            'term' => $this->applyTerm($query, (string) $node['value'], $boolean),
            default => null,
        };
    }

    private function applyConstraint(Builder $query, array $node, string $boolean): void
    {
        $column = $this->aliases[$node['field']] ?? null;

        // An unrecognised column is far more likely to be a typo than an
        // attempt to filter, so fall back to searching for the value itself
        // rather than silently dropping the constraint or returning nothing.
        if ($column === null) {
            $this->applyTerm($query, (string) $node['value'], $boolean);

            return;
        }

        $options = $this->columns[$column];
        $operator = $node['operator'];
        $value = $node['value'];

        if ($value === null) {
            $operator === '!='
                ? $query->whereNotNull($column, $boolean)
                : $query->whereNull($column, $boolean);

            return;
        }

        if ($options['boolean'] ?? false) {
            $truthy = $this->toBoolean($value);
            $query->where($column, $operator === '!=' ? '!=' : '=', $truthy, $boolean);

            return;
        }

        if (($options['date'] ?? false) && $this->isDateOnly($value)) {
            $this->applyDate($query, $column, $operator, $value, $boolean);

            return;
        }

        $query->where($column, $operator, $value, $boolean);
    }

    /**
     * A bare date covers the whole day, so "register_at:2020-01-01" matches
     * anything that happened on it rather than exactly midnight.
     */
    private function applyDate(
        Builder $query,
        string $column,
        string $operator,
        string $value,
        string $boolean,
    ): void {
        $start = $value.' 00:00:00';
        $end = $value.' 23:59:59';

        match ($operator) {
            '=' => $query->whereBetween($column, [$start, $end], $boolean),
            '!=' => $query->whereNotBetween($column, [$start, $end], $boolean),
            '>' => $query->where($column, '>', $end, $boolean),
            '<=' => $query->where($column, '<=', $end, $boolean),
            default => $query->where($column, $operator, $start, $boolean),
        };
    }

    private function applyIn(Builder $query, array $node, string $boolean): void
    {
        $column = $this->aliases[$node['field']] ?? null;

        if ($column === null) {
            return;
        }

        $query->whereIn($column, $node['values'], $boolean);
    }

    /**
     * Free text matches any column flagged as searchable. A model without
     * searchable columns cannot satisfy a bare term at all.
     */
    private function applyTerm(Builder $query, string $value, string $boolean): void
    {
        if ($this->searchable === []) {
            $query->whereRaw('1 = 0', [], $boolean);

            return;
        }

        $escaped = addcslashes($value, '%_\\');

        $query->where(function (Builder $nested) use ($escaped): void {
            foreach ($this->searchable as $column) {
                $nested->orWhere($column, 'like', '%'.$escaped.'%');
            }
        }, null, null, $boolean);
    }

    private function toBoolean(string $value): bool
    {
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private function isDateOnly(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }
}
