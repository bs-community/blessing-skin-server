<?php

namespace App\Models\Concerns;

use App\Services\Search\Applier;
use App\Services\Search\Parser;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filters a model by the query typed into an admin search box.
 *
 * Models opt in by declaring a $searchable definition; see
 * App\Services\Search\Applier for the supported column options.
 */
trait Searchable
{
    public function scopeSearch(Builder $query, ?string $input): Builder
    {
        if ($input === null || trim($input) === '') {
            return $query;
        }

        return (new Applier($this->searchableDefinition()))
            ->apply($query, (new Parser())->parse($input));
    }

    protected function searchableDefinition(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }
}
