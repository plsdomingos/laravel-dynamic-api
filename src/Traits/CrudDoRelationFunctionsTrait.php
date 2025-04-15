<?php

namespace LaravelDynamicApi\Traits;

use LaravelDynamicApi\Common\Constants;
use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
trait CrudDoRelationFunctionsTrait
{
    use CrudDoFunctionsTrait, EngineExecutionFunctions, CommonTrait;

    /**
     * Public function to execute `relationIndex`.
     *
     * @param any model The model to get the relation.
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param array data The validated request data.
     * @param string relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationIndex(
        object $model,
        GenericIndexRequest $request,
        array $data,
        string | null $relationClass
    ): object {
        $relationOutput = $this->relationOutput;
        $relationName = $this->relationName;

        $onlyOne = false;
        $output = Constants::OUTPUT_SIMPLIFIED;
        if (method_exists($model, $relationName)) {
            if ($model->$relationName() instanceof BelongsTo || $model->$relationName() instanceof HasOne) {
                $onlyOne = true;
                $output = Constants::OUTPUT_COMPLETE;
            }
        }

        if (!($relationOutput instanceof Collection)) {
            $visibleHidden = $this->getRelationVisibleAndHiddenExecution(
                $model,
                $this->relationName,
                $relationOutput[0],
                $output
            );
            return $relationOutput->makeVisible($visibleHidden['makeVisible'])->makeHidden($visibleHidden['makeHidden']);
        }

        if (!$relationOutput->isEmpty()) {
            $relationOutput = $this->collectionFilter(
                $relationOutput,
                $relationOutput[0]->pivot,
                json_decode($request->filter),
            );

            if (!$relationOutput->isEmpty()) {
                $visibleHidden = $this->getRelationVisibleAndHiddenExecution(
                    $model,
                    $this->relationName,
                    $relationOutput[0],
                    $output
                );

                // Hide/show the relation fields.
                foreach ($relationOutput as $resultCollection) {
                    foreach ($this->relationClass::WITH_FIELDS as $withField) {
                        if (!in_array($withField, $visibleHidden['makeVisible'])) {
                            continue;
                        }

                        $modelRelationVisibileHidden = $this->getVisibleAndHidden(
                            $this->getModelClass($withField),
                            Constants::OUTPUT_SIMPLIFIED
                        );

                        if ($modelRelationVisibileHidden === null) {
                            continue;
                        }

                        $resultCollection->$withField
                            ->makeVisible($modelRelationVisibileHidden['makeVisible'])
                            ->makeHidden($modelRelationVisibileHidden['makeHidden']);
                    }
                }

                $relationOutput = $relationOutput->makeVisible($visibleHidden['makeVisible'])->makeHidden($visibleHidden['makeHidden']);
            }
        }

        if ($onlyOne) {
            return $relationOutput->first();
        }
        // Sort the relation
        if ($this->sortOrder === 'asc' || $this->sortOrder === 'ASC') {
            $relationOutput = $relationOutput->sortBy($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
        } else {
            $relationOutput = $relationOutput->sortByDesc($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        // Default paginated
        if ($this->paginated === null) {
            $this->paginated = $model::isPaginated($this->type, $this->output);
        }

        if ($this->paginated === true) {
            $range = [$this->perPage * ($this->page - 1), $this->perPage * ($this->page)];
            $this->total = count($relationOutput);
            $result = array_slice($relationOutput->toArray(), $range[0], $range[1] - $range[0]);
            $result = $this->returnPaginatedDetails('', $result, $this->total);
            return $result;
        }

        return $relationOutput;
    }

    /**
     * Execute get relation model of a specific model.
     *
     * @param object model The model to get the relation.
     * @param string $relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationShow(object $model, GenericShowRequest $request, array $data, string $relation, object $relationModel): object
    {
        if ($relationModel) {
            $visibleHidden = $this->getRelationVisibleAndHiddenExecution(
                $model,
                $this->relationName,
                $relationModel,
                Constants::OUTPUT_COMPLETE
            );

            $relationModel = $relationModel->makeVisible($visibleHidden['makeVisible'])->makeHidden($visibleHidden['makeHidden']);

            foreach ($visibleHidden['makeVisible'] as $makeVisible) {
                if (in_array($makeVisible, $relationModel::WITH_FIELDS)) {
                    $relationModel->$makeVisible;
                }
            }
        }
        return $relationModel;
    }

    /**
     * Public function to execute `getRelation`.
     *
     * @param object model The model to get the relation.
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param array data The validated request data.
     * @param string relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationUpdate(object $model, Request $request, array $data, string $relation, $relationModel): object
    {
        return $this->doUpdate($relationModel, $request, $data);
    }

    /**
     * Public function to execute `relationStore`.
     *
     * @param object model The model to get the relation.
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param array data The validated request data.
     * @param string relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationStore(object $model, Request $request, array $data, string $relation): object
    {
        if ($model->$relation() instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
            $models = collect([]);
            if (array_key_exists('ids', $data)) {
                foreach ($data['ids'] as $id) {
                    $models->push($model->users()->syncWithoutDetaching($id));
                }
            } else {
                $models->push($model->users()->syncWithoutDetaching($data['id']));
            }
            return $models;
        }
        if ($model->$relation() instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            $this->relationModel = $model->$relation()->create($data);
            return $this->relationModel;
        }
        return collect(get_class($model->$relation()));
    }

    /**
     * Public function to execute `getRelation`.
     *
     * @param object model The model to get the relation.
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param array data The validated request data.
     * @param string relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationBulkUpdate(object $model, Request $request, array $data, string $relationName): object
    {
        $filter = json_decode($request->filter);
        $models = collect([]);
        if ($filter) {
            if (isset($filter->id)) {
                $relationModels = $filter->id;
                foreach ($relationModels as $relationModel) {
                    $relationOutput = $this->getRelationModelClass($model, $relationName, $relationModel);

                    if (!$relationOutput) {
                        throw new NotFoundHttpException(
                            __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ . ' [' . __LINE__ . '] ' .
                                "Related model $relationModel does not found."
                        );
                    }
                    $models->push($this->doUpdate($relationOutput, $request, $data));
                }
            }
        }
        return $models;
    }

    /**
     * Public function to execute `getRelation`.
     *
     * @param object model The model to get the relation.
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param array data The validated request data.
     * @param string relation The relation name.
     *
     * @return Collection The collection of elements.
     *
     * @todo MakeVisible; MakeHidden; ShowOnly; Sort
     *
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doRelationBulkDestroy(object $model, Request $request, array $data, string $relationName): object
    {
        $deletedModels = collect([]);
        foreach ($data['ids'] as $relationModelId) {
            if ($model->$relationName() instanceof HasMany) {
                $model->$relationName()->where('id', $relationModelId)->delete();
                continue;
            }
            $model->$relationName()->detach($relationModelId);
            $deletedModels->push($this->relationOutput->where('id', $relationModelId)->first());
        }
        return $deletedModels;
    }

    protected function doRelationOfRelationIndex(
        object $model,
        GenericIndexRequest $request,
        array $data,
        string $relationClass,
        object $relationModel,
        string $relationOfRelationClass
    ): object {
        $relationOfRelationOutput = $this->relationOfRelationOutput;
        $relationOfRelationName = $this->relationOfRelationName;

        $onlyOne = false;
        $output = Constants::OUTPUT_SIMPLIFIED;
        if (method_exists($relationModel, $relationOfRelationName)) {
            if ($relationModel->$relationOfRelationName() instanceof BelongsTo || $relationModel->$relationOfRelationName() instanceof HasOne) {
                $onlyOne = true;
                $output = Constants::OUTPUT_COMPLETE;
            }
        }

        // TODO Check first the model then if does not exists get from the relationModel.
        if (!($relationOfRelationOutput instanceof Collection)) {
            $visibleHidden = $this->getRelationVisibleAndHiddenExecution(
                $relationModel,
                $this->relationName,
                $relationOfRelationOutput[0],
                $output
            );
            return $relationOfRelationOutput->makeVisible($visibleHidden['makeVisible'])->makeHidden($visibleHidden['makeHidden']);
        }

        if (!$relationOfRelationOutput->isEmpty()) {
            $relationOfRelationOutput = $this->collectionFilter(
                $this->modelClass,
                $this->relationClass,
                $relationOfRelationOutput,
                $relationOfRelationOutput[0]->pivot,
                json_decode($request->filter),
                $relationOfRelationOutput[0]::IGNORE_FILTERS,
                $relationOfRelationOutput[0]::TERM_FILTERS
            );

            if (!$relationOfRelationOutput->isEmpty()) {
                $visibleHidden = $this->getRelationVisibleAndHiddenExecution(
                    $relationModel,
                    $this->relationName,
                    $relationOfRelationOutput[0],
                    $output
                );

                // Hide/show the relation fields.
                foreach ($relationOfRelationOutput as $resultCollection) {
                    $relationOfRelationModelClass = $this->getModelClass($this->relationOfRelationName);
                    foreach ($relationOfRelationModelClass::WITH_FIELDS as $withField) {
                        if (!in_array($withField, $visibleHidden['makeVisible'])) {
                            continue;
                        }

                        $modelRelationVisibileHidden = $this->getVisibleAndHidden(
                            $this->getModelClass($withField),
                            Constants::OUTPUT_SIMPLIFIED
                        );

                        if ($modelRelationVisibileHidden === null) {
                            continue;
                        }

                        $resultCollection->$withField
                            ->makeVisible($modelRelationVisibileHidden['makeVisible'])
                            ->makeHidden($modelRelationVisibileHidden['makeHidden']);
                    }
                }

                $relationOfRelationOutput = $relationOfRelationOutput->makeVisible($visibleHidden['makeVisible'])->makeHidden($visibleHidden['makeHidden']);
            }
        }

        if ($onlyOne) {
            return $relationOfRelationOutput->first();
        }
        // Sort the relation
        if ($this->sortOrder === 'asc' || $this->sortOrder === 'ASC') {
            $relationOfRelationOutput = $relationOfRelationOutput->sortBy($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
        } else {
            $relationOfRelationOutput = $relationOfRelationOutput->sortByDesc($this->sortBy, SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        // Default paginated
        if ($this->paginated === null) {
            $this->paginated = $relationModel::isPaginated($this->type, $this->output);
        }

        if ($this->paginated === true) {
            $range = [$this->perPage * ($this->page - 1), $this->perPage * ($this->page)];
            $this->total = count($relationOfRelationOutput);
            $result = array_slice($relationOfRelationOutput->toArray(), $range[0], $range[1] - $range[0]);
            $result = $this->returnPaginatedDetails('', $result, $this->total);
            return $result;
        }

        return $relationOfRelationOutput;
    }
}