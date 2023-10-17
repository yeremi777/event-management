<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait CanLoadRelationships
{
    public function loadRelationships(Model|EloquentBuilder|QueryBuilder|HasMany $data, ?array $relations = null): Model|EloquentBuilder|QueryBuilder|HasMany
    {
        $relations = $relations ?? $this->relations ?? [];

        foreach ($relations as $relation) {
            $data->when(
                $this->shouldIncludeRating($relation),
                fn ($q) => $data instanceof Model ? $data->load($relation) : $q->with($relation)
            );
        }

        return $data;
    }

    protected function shouldIncludeRating(string $relation): bool
    {
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);
    }
}
