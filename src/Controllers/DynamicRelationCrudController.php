<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericShowRequest;
use LaravelDynamicApi\Traits\CrudDoRelationFunctionsTrait;
use LaravelDynamicApi\Traits\RouteServiceProviderTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelDynamicApi\Requests\GenericIndexRequest;

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
     * @param \LaravelDynamicApi\Requests\GenericIndexRequest request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse with all elements.
     * 
     * @since 09.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationIndex(GenericIndexRequest $request, string $modelName, string $modelId, string $relationName)
    {
        return $this->execute();
    }

    /**
     * Dynamic get relation model of a specific model function.
     * 
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
     * @param string relationModelId The relation model identifier.
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
     * @param Request request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
     * @param string relationModelId The relation model identifier.
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
     * @param Request request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
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
     * @param Request request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
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

    /**
     * Dynamic bulk delete relation model of a specific model function.
     * 
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
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
     * @param Request request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
     * @param string relationModelId The relation model identifier.
     * @param string function The function name.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationModelFunction(Request $request, string $modelName, string $modelId, string $relationName, string $relationModelId, string $function)
    {
        return $this->execute();
    }

    /**
     * Public function to execute the `relationOfRelationIndex` method dynamicly.
     * 
     * @param \LaravelDynamicApi\Requests\GenericIndexRequest request The request object
     * @param string modelName The model name.
     * @param string modelId The model identifier.
     * @param string relationName The relation model name.
     * @param string relationModelId The relation model identifier.
     * @param string relationOfRelationName The relation of relation name.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicRelationOfRelationIndex(GenericIndexRequest $request, string $modelName, string $modelId, string $relationName, string $relationModelId, string $relationOfRelationName)
    {
        return $this->execute();
    }
}
