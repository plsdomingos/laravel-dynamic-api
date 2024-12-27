<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use Illuminate\Http\Request;

/** Controller parent class.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
abstract class AbstractCrudController extends ControllerExecution
{
    /** ABSTRACT METHODS */
    // CRUD
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
    public abstract function dynamicIndex(GenericIndexRequest $request, string $modelName);
    public abstract function doIndex(string $modelClass, GenericIndexRequest $request, array $data): object;

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
    public abstract function dynamicShow(GenericShowRequest $request, string $modelName, string $modelId);
    public abstract function doShow($model, GenericShowRequest $request, array $data): object;

    /**
     * Execute store.
     *
     * @todo return the model with complete output (COMPLETE_WITH_FIELD)
     * 
     * @return $model Updated mdodel with output visibile and hidden fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public abstract function dynamicStore(Request $request, string $modelName);
    public abstract function doStore(Request $request, array $data): object;

    /**
     * Execute update.
     *
     * @todo return model with complete fields
     * 
     * @return $model Updated mdodel with output visibile and hidden fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public abstract function dynamicUpdate(Request $request, string $modelName, string $modelId);
    public abstract function doUpdate($model, Request $request, array $data): object;

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
    public abstract function dynamicDelete(Request $request, string $modelName, string $modelId);
    public abstract function doDestroy($model, Request $request, array $data): object;

    public abstract function dynamicBulkUpdate(Request $request, string $modelName);
    public abstract function dynamicBulkDelete(Request $request, string $modelName);

    public abstract function dynamicFunction(GenericIndexRequest $request, string $modelName, string $function);
}