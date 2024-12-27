<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use Illuminate\Http\Request;

/** Abstract related crud.
 * 
 * @since 26.12.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
abstract class AbstractRelationCrudController extends ControllerExecution
{
    /** ABSTRACT METHODS */
    // CRUD
    /**
     * Public function to execute `relationIndex`.
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
    public abstract function dynamicRelationIndex(GenericShowRequest $request, string $modelName, string $modelId, string $relationName);
    protected abstract function doRelationIndex(object $model, GenericIndexRequest $request, array $data, string $relation);

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
    public abstract function dynamicRelationShow(GenericShowRequest $request, string $modelName, string $modelId, string $relationName, string $relationModelId);
    protected abstract function doRelationShow(object $model, GenericShowRequest $request, array $data, string $relation, object $relationModel);

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
    public abstract function dynamicRelationUpdate(Request $request, string $modelName, string $modelId, string $relationName, string $relationModelId);
    protected abstract function doRelationUpdate(object $model, Request $request, array $data, string $relation, $relationModel);

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
    public abstract function dynamicRelationStore(Request $request, string $modelName, string $modelId, string $relationName);
    protected abstract function doRelationStore(object $model, Request $request, array $data, string $relation);

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
    public abstract function dynamicRelationBulkUpdate(Request $request, string $modelName, string $modelId, string $relationName);
    protected abstract function doRelationBulkUpdate(object $model, Request $request, array $data, string $relation);

    /**
     * TODO
     * Execute relation function.
     * 
     * @param $model The model to execute the function.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    // protected abstract function doRelationFunction(object $model, Request $request, array $data, string $relation, $relationModel, $function);

    public abstract function dynamicRelationDelete(Request $request, string $modelName, string $modelId, string $relationName);
    public abstract function dynamicRelationModelFunction(GenericShowRequest $request, string $modelName, string $modelId, string $relationName, string $relationModelId, string $function);
}
