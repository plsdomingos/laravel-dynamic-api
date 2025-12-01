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
        mixed $query,
        mixed $filter = null,
        ?string $term = null,
        ?array $ignoreFilters = null,
        ?array $termFilters = null,
        ?array $relationIgnoreFilters = null,
        ?array $relationTermFilters = null,
        ?array $relationOfRelationIgnoreFilters = null,
        ?array $relationOfRelationTermFilters = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?object $relationOfRelationClass = null,
        ?object $relationOfRelationName = null,
        mixed $sortBy = null,
        mixed $sortOrder = null,
        mixed $sortByRaw = null,
        ?array $ignoreSort = null,
        ?array $relationIgnoreSort = null,
        ?array $relationOfRelationIgnoreSort = null,
        ?int $page = null,
        ?int $perPage = null,
    ): mixed {
        // set values
        $request = $request ?? $this->request;
        $data = empty($data) ? $this->data : $data;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $relationOfRelationClass = $relationOfRelationClass ?? $this->relationOfRelationClass;
        $relationOfRelationName = $relationOfRelationName ?? $this->relationOfRelationName;
        $locale = $locale ?? $this->locale;
        $ignoreFilters = $ignoreFilters ?? $modelClass::IGNORE_FILTERS;
        $termFilters = $termFilters ?? $modelClass::TERM_FILTERS;
        $relationIgnoreFilters = $relationIgnoreFilters ?? $relationClass ? $relationClass::IGNORE_FILTERS : [];
        $relationTermFilters = $relationTermFilters ?? $relationClass ? $relationClass::TERM_FILTERS : [];
        $relationOfRelationIgnoreFilters = $relationOfRelationIgnoreFilters ?? $relationOfRelationClass ? $relationOfRelationClass::IGNORE_FILTERS : [];
        $relationOfRelationTermFilters = $relationOfRelationTermFilters ?? $relationOfRelationClass ? $relationOfRelationClass::TERM_FILTERS : [];
        $ignoreSort = $ignoreSort ?? $modelClass::IGNORE_SORT;
        $relationIgnoreSort = $relationIgnoreSort ?? $relationClass ? $relationClass::IGNORE_SORT : [];
        $relationOfRelationIgnoreSort = $relationOfRelationIgnoreSort ?? $relationOfRelationClass ?  $relationOfRelationClass::IGNORE_SORT : [];
        $sortBy = $sortBy ?? $this->sortBy;
        $sortOrder = $sortOrder ?? $this->sortOrder;
        $sortByRaw = $sortByRaw ?? $this->sortByRaw;
        $page = $page ?? $this->page;
        $perPage = $perPage ?? $this->perPage;
        $term = $term ?? $this->term;

        if ($filter) {
            foreach ($filter as $key => $val) {
                if (in_array($key, $ignoreFilters)) {
                    continue;
                }
                if ($key === 'term') {
                    $query = $modelClass::requestFilterByTerm($modelClass, $query, $val, $termFilters, $relationTermFilters);
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
            $query = $modelClass::requestFilterByTerm($modelClass, $query, $term, $termFilters, $relationTermFilters);
        }

        $query = $modelClass::requestFilter(
            $query,
            $filter,
            $term,
            $ignoreFilters,
            $termFilters,
            $relationIgnoreFilters,
            $relationTermFilters,
            $relationOfRelationIgnoreFilters,
            $relationOfRelationTermFilters,
            $modelClass,
            $modelName,
            $model,
            $relationClass,
            $relationName,
            $relationModel,
            $relationOfRelationClass,
            $relationOfRelationName,
            $sortBy,
            $sortOrder,
            $sortByRaw,
            $ignoreSort,
            $relationIgnoreSort,
            $relationOfRelationIgnoreSort,
            $page,
            $perPage,
            $this->authUser,
        );

        return $query;
    }

    /**
     * Sort the results.
     * 
     * @since 19.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function requestSort(
        mixed $query,
        mixed $filter = null,
        ?string $term = null,
        ?array $ignoreFilters = null,
        ?array $termFilters = null,
        ?array $relationIgnoreFilters = null,
        ?array $relationTermFilters = null,
        ?array $relationOfRelationIgnoreFilters = null,
        ?array $relationOfRelationTermFilters = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?object $relationOfRelationClass = null,
        ?object $relationOfRelationName = null,
        mixed $sortBy = null,
        mixed $sortOrder = null,
        mixed $sortByRaw = null,
        ?array $ignoreSort = null,
        ?array $relationIgnoreSort = null,
        ?array $relationOfRelationIgnoreSort = null,
        ?int $page = null,
        ?int $perPage = null,
    ): mixed {
        // set values
        $request = $request ?? $this->request;
        $data = empty($data) ? $this->data : $data;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $relationOfRelationClass = $relationOfRelationClass ?? $this->relationOfRelationClass;
        $relationOfRelationName = $relationOfRelationName ?? $this->relationOfRelationName;
        $locale = $locale ?? $this->locale;
        $ignoreFilters = $ignoreFilters ?? $modelClass::IGNORE_FILTERS;
        $termFilters = $termFilters ?? $modelClass::TERM_FILTERS;
        $relationIgnoreFilters = $relationIgnoreFilters ?? $relationClass ? $relationClass::IGNORE_FILTERS : [];
        $relationTermFilters = $relationTermFilters ?? $relationClass ? $relationClass::TERM_FILTERS : [];
        $relationOfRelationIgnoreFilters = $relationOfRelationIgnoreFilters ?? $relationOfRelationClass ? $relationOfRelationClass::IGNORE_FILTERS : [];
        $relationOfRelationTermFilters = $relationOfRelationTermFilters ?? $relationOfRelationClass ? $relationOfRelationClass::TERM_FILTERS : [];
        $ignoreSort = $ignoreSort ?? $modelClass::IGNORE_SORT;
        $relationIgnoreSort = $relationIgnoreSort ?? $relationClass ? $relationClass::IGNORE_SORT : [];
        $relationOfRelationIgnoreSort = $relationOfRelationIgnoreSort ?? $relationOfRelationClass ?  $relationOfRelationClass::IGNORE_SORT : [];
        $sortBy = $sortBy ?? $this->sortBy;
        $sortOrder = $sortOrder ?? $this->sortOrder;
        $sortByRaw = $sortByRaw ?? $this->sortByRaw;
        $page = $page ?? $this->page;
        $perPage = $perPage ?? $this->perPage;
        $term = $term ?? $this->term;

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
            $query,
            $filter,
            $term,
            $ignoreFilters,
            $termFilters,
            $relationIgnoreFilters,
            $relationTermFilters,
            $relationOfRelationIgnoreFilters,
            $relationOfRelationTermFilters,
            $modelClass,
            $modelName,
            $model,
            $relationClass,
            $relationName,
            $relationModel,
            $relationOfRelationClass,
            $relationOfRelationName,
            $sortBy,
            $sortOrder,
            $sortByRaw,
            $ignoreSort,
            $relationIgnoreSort,
            $relationOfRelationIgnoreSort,
            $page,
            $perPage,
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
        mixed $query,
        mixed $pivot,
        mixed $filter = null,
        ?string $term = null,
        ?array $ignoreFilters = null,
        ?array $termFilters = null,
        ?array $relationIgnoreFilters = null,
        ?array $relationTermFilters = null,
        ?array $relationOfRelationIgnoreFilters = null,
        ?array $relationOfRelationTermFilters = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?object $relationOfRelationClass = null,
        ?object $relationOfRelationName = null,
        mixed $sortBy = null,
        mixed $sortOrder = null,
        mixed $sortByRaw = null,
        ?array $ignoreSort = null,
        ?array $relationIgnoreSort = null,
        ?array $relationOfRelationIgnoreSort = null,
        ?int $page = null,
        ?int $perPage = null,
    ): mixed {
        // set values
        $request = $request ?? $this->request;
        $data = empty($data) ? $this->data : $data;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $relationOfRelationClass = $relationOfRelationClass ?? $this->relationOfRelationClass;
        $relationOfRelationName = $relationOfRelationName ?? $this->relationOfRelationName;
        $locale = $locale ?? $this->locale;
        $ignoreFilters = $ignoreFilters ?? $modelClass::IGNORE_FILTERS;
        $termFilters = $termFilters ?? $modelClass::TERM_FILTERS;
        $relationIgnoreFilters = $relationIgnoreFilters ?? $relationClass::IGNORE_FILTERS;
        $relationTermFilters = $relationTermFilters ?? $relationClass::TERM_FILTERS;
        $relationOfRelationIgnoreFilters = $relationOfRelationIgnoreFilters ?? $relationOfRelationClass ? $relationOfRelationClass::IGNORE_FILTERS : [];
        $relationOfRelationTermFilters = $relationOfRelationTermFilters ?? $relationOfRelationClass ? $relationOfRelationClass::TERM_FILTERS : [];
        $ignoreSort = $ignoreSort ?? $modelClass::IGNORE_SORT;
        $relationIgnoreSort = $relationIgnoreSort ?? $relationClass::IGNORE_SORT;
        $relationOfRelationIgnoreSort = $relationOfRelationIgnoreSort ?? $relationOfRelationClass ?  $relationOfRelationClass::IGNORE_SORT : [];
        $sortBy = $sortBy ?? $this->sortBy;
        $sortOrder = $sortOrder ?? $this->sortOrder;
        $sortByRaw = $sortByRaw ?? $this->sortByRaw;
        $page = $page ?? $this->page;
        $perPage = $perPage ?? $this->perPage;
        $term = $term ?? $this->term;

        if ($relationOfRelationClass) {
            $query = $relationOfRelationClass::collectionFilter(
                $query,
                $pivot,
                $filter,
                $term,
                $ignoreFilters,
                $termFilters,
                $relationIgnoreFilters,
                $relationTermFilters,
                $relationOfRelationIgnoreFilters,
                $relationOfRelationTermFilters,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $sortBy,
                $sortOrder,
                $sortByRaw,
                $ignoreSort,
                $relationIgnoreSort,
                $relationOfRelationIgnoreSort,
                $page,
                $perPage,
                $this->authUser,
            );
        }

        $query = $relationClass::collectionFilter(
            $query,
            $pivot,
            $filter,
            $term,
            $ignoreFilters,
            $termFilters,
            $relationIgnoreFilters,
            $relationTermFilters,
            $relationOfRelationIgnoreFilters,
            $relationOfRelationTermFilters,
            $modelClass,
            $modelName,
            $model,
            $relationClass,
            $relationName,
            $relationModel,
            $relationOfRelationClass,
            $relationOfRelationName,
            $sortBy,
            $sortOrder,
            $sortByRaw,
            $ignoreSort,
            $relationIgnoreSort,
            $relationOfRelationIgnoreSort,
            $page,
            $perPage,
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
                    $query = $relationClass::whereIn('id', $queryOutput->pluck('id'))->get();
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
        $makeVisible = [];
        $makeHidden = [];
        if ($this->withTranslations === true) {
            array_push($makeVisible, 'translations');
        } else 
        if ($this->withTranslations === false) {
            // ignore   
        } else {
            switch ($output) {
                case Constants::OUTPUT_COMPLETE:
                case Constants::OUTPUT_EXTENSIVE:
                    array_push($makeVisible, 'translations');
                    break;
                case Constants::OUTPUT_SIMPLIFIED:
                default:
                    array_push($makeHidden, 'translations');
            }
        }

        $relationModelClass = $this->getModelClass($relation);
        $makeVisible = array_merge($makeVisible, $model::getRelationVisibleFields($relation, $relationModelClass, $output));

        return [
            'makeVisible' => $makeVisible,
            'makeHidden' => array_merge($makeHidden, $relationModelClass::getRelationHiddenFields($relation, $relationModelClass, $output, $makeVisible))
        ];
    }
}
