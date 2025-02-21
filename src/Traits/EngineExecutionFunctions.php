<?php

namespace LaravelDynamicApi\Traits;

use LaravelDynamicApi\Common\Constants;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Engine execution functions
 * 
 * @since 03.03.2023
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait EngineExecutionFunctions
{
    /**
     * Filter the results before get.
     * 
     * @param array|null $filter Filter condition where the key is the field and the value is the field value.
     * @return mixed $query The query with the filters
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function requestFilter(
        string $modelClass,
        mixed $query,
        mixed $filter = null,
        string $term = null,
        array $ignoreFilters = null,
        array $termFilters = null,
        array $relationTermFilters = null,
        mixed $sortBy = null,
        mixed $sortOrder = null,
        int $page = null,
        int $perPage = null,
    ): mixed {
        // Set fields
        $ignoreFilters = $ignoreFilters ?? $modelClass::IGNORE_FILTERS;
        $termFilters = $termFilters ?? $modelClass::TERM_FILTERS;
        $relationTermFilters = $relationTermFilters ?? $modelClass::RELATION_TERM_FILTERS;
        $sortBy = $sortBy ?? $this->sortBy;
        $sortOrder = $sortOrder ?? $this->sortOrder;
        $page = $page ?? $this->page;
        $perPage = $perPage ?? $this->perPage;
        $term = $term ?? $this->term;

        $query = $modelClass::requestFilter(
            $modelClass,
            $query,
            $filter,
            $term,
            $sortBy,
            $sortOrder,
            $page,
            $perPage,
            $this->authUser,
        );

        if ($filter) {
            foreach ($filter as $key => $val) {
                if (in_array($key, $ignoreFilters)) {
                    continue;
                }
                if ($key === 'term') {
                    $query = $this->requestFilterByTerm($modelClass, $query, $val, $termFilters, $relationTermFilters);
                    continue;
                }
                if (is_array($val)) {
                    $query = $query->whereIn($key, $val);
                } else {
                    $query = $query->where($key, $val);
                }
            }
        }

        if ($term) {
            $query = $this->requestFilterByTerm($modelClass, $query, $term, $termFilters, $relationTermFilters);
        }

        return $query;
    }

    private function requestFilterByTerm(
        string $modelClass,
        mixed $query,
        string $term,
        array $termFilters,
        array $relationTermFilters,
    ) {
        return $query->where(function ($q) use ($termFilters, $relationTermFilters, $modelClass, $term) {
            $q->where('id', 'like', '%' . $term . '%');
            foreach ($termFilters as $termFilter) {
                if (in_array($termFilter, $modelClass::TRANSLATED_FIELDS)) {
                    $q->orWhereTranslationLike($termFilter, '%' . $term . '%');
                } else {
                    $q->orWhere($termFilter, 'like', '%' . $term . '%');
                }
            }
            foreach ($relationTermFilters as $relation => $relationTermFilter) {
                foreach ($relationTermFilter as $termFilter) {
                    $q->orWhereHas($relation, function ($qRelation) use ($relation, $termFilter, $term) {
                        if (in_array($termFilter, $this->getModelClass($relation)::TRANSLATED_FIELDS)) {
                            $qRelation->whereTranslationLike($termFilter, '%' . $term . '%');
                        } else {
                            $qRelation->where($termFilter, 'like', '%' . $term . '%');
                        }
                    });
                }
            }
        });
    }

    /**
     * Sort the results.
     * 
     * @since 19.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function requestSort(
        string $modelClass,
        mixed $query,
        mixed $sortBy = null,
        mixed $sortOrder = null,
        array $ignoreSort = null,
        string $sortByRaw = null
    ): mixed {
        // Set fields
        $ignoreSort = $ignoreSort ?? $modelClass::IGNORE_SORT;
        $sortBy = $sortBy ?? $this->sortBy;
        $sortOrder = $sortOrder ?? $this->sortOrder;

        // TODO: Sort by appends
        if (
            $sortBy &&
            !in_array($this->sortBy, $modelClass::TRANSLATED_FIELDS) &&
            !in_array($this->sortBy, $modelClass::APPEND_FIELDS) &&
            !in_array($this->sortBy, $ignoreSort)
        ) {
            $query = $modelClass::orderBy($sortBy, $sortOrder);
        }

        if ($sortByRaw) {
            $query = $query->orderByRaw($sortByRaw);
        }

        $query = $modelClass::requestSort(
            $modelClass,
            $query,
            $sortBy,
            $sortOrder,
            $this->authUser,
        );

        return $query;
    }

    /**
     * Filter the results after get.
     * 
     * @param array|null $filter Filter condition where the key is the field and the value is the field value.
     * @return mixed $query The query with the filters
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function collectionFilter(
        string $modelClass,
        string $modelRelationClass,
        mixed $query,
        mixed $pivot,
        $filter = null,
        $ignoreFilters = [],
        $termFilters = []
    ): mixed {

        $query = $modelRelationClass::collectionFilter(
            $modelRelationClass,
            $query,
            $filter,
            $this->authUser,
        );

        $isDeleted = null;
        if ($filter) {
            foreach ($filter as $key => $val) {
                if ($key === 'deleted') {
                    $isDeleted = $val;
                }
                if (in_array($key, $ignoreFilters)) {
                    continue;
                }
                if ($key === 'term') {
                    $queryOutput = collect([]);
                    foreach ($termFilters as $termFilter) {
                        $queryOutput = $queryOutput->merge($query->filter(function ($q) use ($termFilter, $val) {
                            return Str::contains(Str::lower($q[$termFilter]), Str::lower($val));
                        }));
                    }
                    // Transformed the query output to a model collection.
                    $query = $modelRelationClass::whereIn('id', $queryOutput->pluck('id'))->get();
                    continue;
                }
                if ($pivot && array_key_exists('school_id', $pivot->toArray())) {
                    $key = 'pivot.' . $key;
                }
                if (is_array($val)) {
                    $query = $query->whereIn($key, $val);
                } else {
                    $query = $query->where($key, $val);
                }
            }
        }

        if ($isDeleted === true) {
            $queryOutput = $query->filter(function ($q) {
                return $q->deleted_at !== null && $q->deleted_at !== '';
            });
            $query = $queryOutput;
        } else {
            $queryOutput = $query->filter(function ($q) {
                return $q->deleted_at === null || $q->deleted_at === '';
            });
            $query = $queryOutput;
        }
        return $query->values();
    }

    /**
     * Get the realtion model class.
     * 
     * @param $model The main model to get the relation.
     * @param string $relation The relation name.
     * @param $relationModel The relation model key.
     * 
     * @since 10.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function getRelationModelClass($model, string $relation, $relationModel): object
    {
        $relationOutput = $model->$relation;

        if ($relationOutput === null) {
            throw new NotFoundHttpException(
                __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    "Relation $relation does not exists."
            );
        }

        if (is_numeric($relationModel)) {
            $relationOutput = $relationOutput->where('id', $relationModel);
        } else {
            $relationOutput = $relationOutput->where('slug', $relationModel);
        }

        if ($relationOutput) {
            $visibleHidden = $this->getRelationVisibleAndHiddenExecution($model, $relation, $relationOutput, Constants::OUTPUT_COMPLETE);

            $relationOutput = $relationOutput
                ->makeVisible($visibleHidden['makeVisible'])
                ->makeHidden($visibleHidden['makeHidden'])->first();
        }

        return $relationOutput;
    }

    /**
     * Get function name.
     * 
     * @param string $function The function name.
     * @param Object $model The model object.
     * 
     * @return string The function name.
     */
    protected function getFunctionName(string $function, object $model): string
    {
        if (array_key_exists($function, $model::FUNCTIONS)) {
            return $model::FUNCTIONS[$function];
        }
        return 'execute' . Str::studly($function);
    }

    /**
     * Get relation visible and hidden fields.
     * 
     * @param $model The model to get the relation.
     * @param string $relation The relation name.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getRelationVisibleAndHiddenExecution($model, string $relation, $relationOutput, $output): array
    {
        $relationModelClass = $this->getModelClass($relation);
        $makeVisible = $model::getRelationVisibleFields($relation, $relationModelClass, $output);

        return [
            'makeVisible' => $makeVisible,
            'makeHidden' => $relationModelClass::getRelationHiddenFields($relation, $relationModelClass, $output, $makeVisible)
        ];
    }
}
