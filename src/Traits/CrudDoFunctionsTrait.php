<?php

namespace LaravelDynamicApi\Traits;

use App\Traits\EngineCacheFunctions;
use LaravelDynamicApi\Common\Constants;
use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Common Trait.
 * 
 * Common functions used arround the code.
 * 
 * Used by Controller.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait CrudDoFunctionsTrait
{
    use CommonTrait,
        EngineExecutionFunctions,
        ReferenceDataTrait,
        EngineCacheFunctions;

    /**
     * Public function to execute `index`.
     * 
     * @param string modelClass The model class name.
     * @param \LaravelDynamicApi\Requests\GenericIndexRequest request The request object.
     * @param array data Array with the validated request,
     * 
     * @return Collection The collection of elements.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     * @todo implement term without admin.
     * @todo implement filter in the request and create data.
     */
    public function doIndex(string $modelClass, GenericIndexRequest $request, array $data): object
    {
        // Check if the request is cached
        $cachedValue = $this->getCache($modelClass, $this->type, $request);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $query = $modelClass::query();

        $query = $this->requestFilter(
            $query,
            json_decode($request->filter, true)
        );

        $query = $this->requestSort(
            $query,
            json_decode($request->filter, true)
        );

        $visibleHidden = $this->getVisibleAndHidden(
            $modelClass,
            $this->output,
            null,
            $this->requestOutput,
            $this->makeVisible,
            $this->makeHidden,
            $this->withTranslations,
            $this->showOnly,
            $this->with,
            $this->withCount
        );

        $with = array_merge($modelClass::getWithFields($this->output), $this->with);
        $withCount = array_merge($modelClass::getWithCountFields($this->output), $this->withCount);

        // Default paginated
        if ($this->paginated === null) {
            $this->paginated = $modelClass::isPaginated($this->type, $this->output);
        }

        $query = $query->with($with)->withCount($withCount);

        if (in_array($this->sortBy, $modelClass::TRANSLATED_FIELDS) || in_array($this->sortBy, $modelClass::APPEND_FIELDS)) {
            $result = $query
                ->get()
                ->makeVisible($visibleHidden['makeVisible'])
                ->makeHidden($visibleHidden['makeHidden']);

            if ($this->sortOrder === 'asc' || $this->sortOrder === 'ASC') {
                $result = $result->sortBy($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
            } else {
                $result = $result->sortByDesc($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
            }

            if ($this->paginated === true) {
                $range = [$this->perPage * ($this->page - 1), $this->perPage * ($this->page)];
                $this->total = count($result);
                $result = $result->slice($range[0], $range[1] - $range[0]);

                // Hide/show the relation fields.
                foreach ($result as $resultCollection) {
                    foreach ($modelClass::WITH_FIELDS as $withField) {
                        if ($this->ignoreRelation($withField, $visibleHidden, $resultCollection)) {
                            continue;
                        }

                        $onlyOne = $this->isOnlyOne($withField, $resultCollection);
                        $withFieldUpdated =  $this->returnRelation($resultCollection, $withField, $visibleHidden[$withField]);

                        unset($resultCollection->$withField);
                        $resultCollection->$withField = $withFieldUpdated;
                        if ($onlyOne) {
                            $resultCollection->$withField = $resultCollection->$withField->first();
                        }
                    }
                }
                // TODO: Solve url params problem.
                $result = $this->returnPaginatedDetails('', $result->values()->toArray(), $this->total);
            } else {
                // Hide/show the relation fields.
                foreach ($result as $resultCollection) {
                    foreach ($modelClass::WITH_FIELDS as $withField) {
                        if ($this->ignoreRelation($withField, $visibleHidden, $resultCollection)) {
                            continue;
                        }
                        $onlyOne = $this->isOnlyOne($withField, $resultCollection);
                        $withFieldUpdated =  $this->returnRelation($resultCollection, $withField, $visibleHidden[$withField]);

                        unset($resultCollection->$withField);
                        $resultCollection->$withField = $withFieldUpdated;
                        if ($onlyOne) {
                            $resultCollection->$withField = $resultCollection->$withField->first();
                        }
                    }
                }
            }
        } else {
            if ($this->paginated == true) {
                // Set returned page.
                Paginator::currentPageResolver(function () {
                    return $this->page;
                });
                $result = $query
                    ->paginate($this->perPage)->withQueryString();

                $result->setCollection($result->getCollection()
                    ->makeVisible($visibleHidden['makeVisible'])
                    ->makeHidden($visibleHidden['makeHidden']));

                // Hide/show the relation fields.
                foreach ($result->getCollection() as $resultCollection) {
                    foreach ($modelClass::WITH_FIELDS as $withField) {
                        if ($this->ignoreRelation($withField, $visibleHidden, $resultCollection)) {
                            continue;
                        }

                        $onlyOne = $this->isOnlyOne($withField, $resultCollection);

                        $withFieldUpdated =  $this->returnRelation($resultCollection, $withField, $visibleHidden[$withField]);

                        unset($resultCollection->$withField);
                        $resultCollection->$withField = $withFieldUpdated;
                        if ($onlyOne) {
                            $resultCollection->$withField = $resultCollection->$withField->first();
                        }
                    }
                }

                // Add query
                $result = collect($result);
                $result->put('query', substr(str_replace($this->request->url(), '', $this->request->fullUrl()), 1));
            } else {
                $result = $query
                    ->get()
                    ->makeVisible($visibleHidden['makeVisible'])
                    ->makeHidden($visibleHidden['makeHidden']);

                // Hide/show the relation fields.
                foreach ($result as $resultCollection) {
                    foreach ($modelClass::WITH_FIELDS as $withField) {
                        if ($this->ignoreRelation($withField, $visibleHidden, $resultCollection)) {
                            continue;
                        }
                        $onlyOne = $this->isOnlyOne($withField, $resultCollection);
                        $withFieldUpdated =  $this->returnRelation($resultCollection, $withField, $visibleHidden[$withField]);

                        unset($resultCollection->$withField);
                        $resultCollection->$withField = $withFieldUpdated;
                        if ($onlyOne) {
                            $resultCollection->$withField = $resultCollection->$withField->first();
                        }
                    }
                }
            }
        }

        // Save the output in the cache
        $this->saveCache($modelClass, $this->type, $request, $result);
        return $result;
    }

    /**
     * Public function to execute `show`.
     * 
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param any model The model to return.
     * @param array data The validated request data.
     * 
     * @return Collection The collection of elements.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doShow($model, GenericShowRequest $request, array $data): object
    {
        // Check if the request is cached
        $cachedValue = $this->getCache($this->modelClass, $this->type, $request);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $visibleHidden = $this->getVisibleAndHidden(
            $model,
            $data['output'] ?? Constants::OUTPUT_COMPLETE,
            null,
            $this->requestOutput,
            $this->makeVisible,
            $this->makeHidden,
            $this->withTranslations,
            $this->showOnly,
            $this->with,
            $this->withCount
        );

        $response = $model
            ->makeVisible($visibleHidden['makeVisible'])
            ->makeHidden($visibleHidden['makeHidden']);

        // Hide/show the relation fields.
        // Same function in doUpodate. Create a generic one.
        foreach ($model::WITH_FIELDS as $withField) {
            if (!in_array($withField, $visibleHidden['makeVisible'])) {
                continue;
            }
            $onlyOne = false;

            if ($response->$withField() instanceof BelongsTo || $response->$withField() instanceof HasOne) {
                $onlyOne = true;
            }

            $withFieldToGet = [];
            if ($response->$withField()->first()) {
                $withFieldToGet = get_class($response->$withField()->first())::WITH_FIELDS;
            }

            $withFieldUpdated = $response->$withField()
                ->with($withFieldToGet)
                ->withCount($withFieldToGet)->get()
                ->makeVisible($visibleHidden[$withField]['makeVisible'])
                ->makeHidden($visibleHidden[$withField]['makeHidden']);

            unset($response->$withField);
            $response->$withField = $withFieldUpdated;

            if ($onlyOne) {
                $response->$withField = $response->$withField->first();
            }
        }

        // Save the output in the cahce
        $this->saveCache($this->modelClass, $this->type, $request, $response);
        return $response;
    }

    /**
     * Execute store.
     * 
     * @return $model Updated mdodel with output visibile and hidden fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doStore(Request $request, array $data): object
    {
        $createdModel = $this->modelClass::create($data);
        $this->specificModel = true;

        $visibleHidden = $this->getVisibleAndHidden($createdModel, Constants::OUTPUT_COMPLETE);
        return $this->model = $createdModel->fresh()
            ->load($createdModel::COMPLETE_WITH_FIELDS)
            ->loadCount($createdModel::WITH_FIELDS)
            ->makeVisible($visibleHidden['makeVisible'])
            ->makeHidden($visibleHidden['makeHidden']);
    }

    /**
     * Execute update.
     * 
     * @return object $model Updated mdodel with output visibile and hidden fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doUpdate($model, Request $request, array $data): object
    {
        $model->update($data);
        $visibleHidden = $this->getVisibleAndHidden($model, Constants::OUTPUT_COMPLETE);

        // Force Update updated_at
        $model->touch();

        $response = $model->fresh()
            ->load($model::COMPLETE_WITH_FIELDS)
            ->loadCount($model::WITH_FIELDS)
            ->makeVisible($visibleHidden['makeVisible'])
            ->makeHidden($visibleHidden['makeHidden']);

        // Hide/show the relation fields.
        // Same function in doShow. Create a generic one.
        foreach ($model::WITH_FIELDS as $withField) {
            if (!in_array($withField, $visibleHidden['makeVisible'])) {
                continue;
            }
            $onlyOne = false;

            if ($response->$withField() instanceof BelongsTo || $response->$withField() instanceof HasOne) {
                $onlyOne = true;
            }

            $withFieldToGet = [];
            if ($response->$withField()->first()) {
                $withFieldToGet = get_class($response->$withField()->first())::WITH_FIELDS;
            }

            $withFieldUpdated = $response->$withField()->with($withFieldToGet)->get()
                ->makeVisible($visibleHidden[$withField]['makeVisible'])
                ->makeHidden($visibleHidden[$withField]['makeHidden']);

            unset($response->$withField);
            $response->$withField = $withFieldUpdated;


            if ($onlyOne) {
                $response->$withField = $response->$withField->first();
            }
        }

        // Delete cache
        $this->deleteCache($this->modelClass, $this->type, $request);
        return $response;
    }

    /**
     * Execute destroy.
     * 
     * @param $model Model to destroy.
     * 
     * @return object $model The deleted model.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doDestroy($model, Request $request, array $data): object
    {
        $model->delete();

        // Delete cache
        $this->deleteCache($this->modelClass, $this->type, $request);
        return $model;
    }

    /**
     * Execute destroy.
     * 
     * @param $model Model to destroy.
     * 
     * @return $model The deleted model.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doBulkDestroy(string $modelClass, Request $request, array $data): object
    {
        $deletedModels = collect([]);
        $visibleHidden = $this->getVisibleAndHidden($modelClass, Constants::OUTPUT_SIMPLIFIED);
        $models = $modelClass::query();
        if ($this->withTrashed) {
            $models->withTrashed();
        }
        $models = $models->whereIn('id', $data['ids'])->get();
        foreach ($models as $model) {
            $model = $this->doDestroy($model, $request, $data);
            // Force Update updated_at
            $model->touch();

            $deletedModels->push($model->fresh()
                ->makeVisible($visibleHidden['makeVisible'])
                ->makeHidden($visibleHidden['makeHidden']));
        }
        return $deletedModels;
    }

    /**
     * Execute bulk update.
     * 
     * @return $model Updated mdodel with output visibile and hidden fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doBulkUpdate(string $modelClass, Request $request, array $data): object
    {
        $upatedMmodels = collect([]);
        $visibleHidden = $this->getVisibleAndHidden($modelClass, Constants::OUTPUT_SIMPLIFIED);
        $models = $modelClass::query();
        if ($this->withTrashed) {
            $models->withTrashed();
        }
        $models = $models->whereIn('id', $data['ids'])->get();
        foreach ($models as $model) {
            $model = $this->doUpdate($model, $request, $data);
            // Force Update updated_at
            $model->touch();

            $upatedMmodels->push($model->fresh()
                ->makeVisible($visibleHidden['makeVisible'])
                ->makeHidden($visibleHidden['makeHidden']));
        }
        return $upatedMmodels;
    }

    /**
     * Execute function.
     * 
     * @return Collection The collection of elements.
     * 
     * @since 07.10.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doFunction(string $modelClass, GenericIndexRequest $request, array $data, string $function): object
    {
        return $modelClass::$function(
            $request,
            $data,
            $this->modelClass,
            $this->modelName,
            $this->relationClass,
            $this->relationName,
            $this->locale,
            $this->authUser
        );
    }

    /**
     * Execute model function.
     * 
     * @return Collection The collection of elements.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function doModelFunction($model, Request $request, array $data, string $function): object
    {
        return $model->$function(
            $request,
            $data,
            $this->modelClass,
            $this->modelName,
            $this->model,
            $this->relationClass,
            $this->relationName,
            $this->relationModel,
            $this->locale,
            $this->authUser
        );
    }

    private function ignoreRelation($withField, $visibleHidden, $resultCollection)
    {
        if (!in_array($withField, $visibleHidden['makeVisible'])) {
            return true;
        }
        if (!$resultCollection->$withField()->first()) {
            return true;
        }
        return false;
    }

    private function isOnlyOne($withField, $resultCollection)
    {
        if ($resultCollection->$withField() instanceof BelongsTo || $resultCollection->$withField() instanceof HasOne) {
            return true;
        }
        return false;
    }
}
