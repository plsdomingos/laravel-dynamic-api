<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericShowRequest;
use LaravelDynamicApi\Traits\CrudDoRelationFunctionsTrait;
use LaravelDynamicApi\Traits\RouteServiceProviderTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Dynamic related crud controller.
 * 
 * All API controllers must extend to this class.
 * 
 * This class includes the methods: index, show and get relation.
 * 
 * @since 26.12.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class DynamicRelationCrudController extends AbstractRelationCrudController
{
    use CrudDoRelationFunctionsTrait;
    use RouteServiceProviderTrait;

    /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Public function to execute the `relationIndex` method dynamicly.
     * 
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param string modelClass The model class name.
     * @param any model The model to return.
     * @param string relation The model relation.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationIndex(GenericShowRequest $request, string $modelName, string $modelId, string $relationName)
    {
        return $this->execute();
    }

    /**
     * Dynamic get relation model of a specific model function.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationShow(GenericShowRequest $request, string $modelName, string $modelId, string $relationName, string $relationModelId)
    {
        return $this->execute();
    }

    /**
     * Dynamic get relation model of a specific model function.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationUpdate(Request $request, string $modelName, string $modelId, string $relationName, string $relationModelId)
    {
        return $this->execute();
    }

    /**
     * Dynamic store relation model.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationStore(Request $request, string $modelName, string $modelId, string $relationName)
    {
        return $this->execute();
    }

    /**
     * Dynamic bulk updtea relation model of a specific model function.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationBulkUpdate(Request $request, string $modelName, string $modelId, string $relationName)
    {
        return $this->execute();
    }

    // dynamicRelationDelete 
    /**
     * Dynamic bulk updtea relation model of a specific model function.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationDelete(Request $request, string $modelName, string $modelId, string $relationName)
    {
        return $this->execute();
    }


    /**
     * Generic function to run a relation model function.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationModelFunction(GenericShowRequest $request, string $modelName, string $modelId, string $relationName, string $relationModelId, string $function)
    {
        return $this->execute();
    }
}
