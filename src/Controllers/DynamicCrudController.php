<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use LaravelDynamicApi\Traits\CrudDoFunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Controller parent class.
 * 
 * All API controllers must extend to this class.
 * 
 * This class includes the methods: index, show and get relation.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class DynamicCrudController extends AbstractCrudController
{
    use CrudDoFunctionsTrait;

    /**
     * Constructor.
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Public function to execute the `index` method dynamicly.
     * 
     * @param \LaravelDynamicApi\Requests\GenericIndexRequest request The request object.
     * @param string modelClass The class name of the model you want to get the elements.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 26.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicIndex(GenericIndexRequest $request, string $modelName)
    {
        return $this->execute();
    }

    /**
     * Public function to execute the `show` method dynamicly.
     * 
     * @param \LaravelDynamicApi\Requests\GenericShowRequest request The request object
     * @param string modelClass The class name of the model you want to show.
     * @param model The model route key.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse with all elements.
     * 
     * @since 26.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicShow(GenericShowRequest $request, string $modelName, string $modelId)
    {
        return $this->execute();
    }

    /**
     * Generic store function to create one element.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 26.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicStore(Request $request, string $modelName)
    {
        return $this->execute();
    }

    /**
     * Generic update function to update one element.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 26.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicUpdate(Request $request, string $modelName, string $modelId)
    {
        return $this->execute();
    }

    /**
     * Generic update function to update multiple elements.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 23.12.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicBulkUpdate(Request $request, string $modelName)
    {
        return $this->execute();
    }

    /**
     * Dynamic delete function to delete one element.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicDelete(Request $request, string $modelName, string $modelId)
    {
        return $this->execute();
    }

    /**
     * Dynamic delete function to delete one element.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicBulkDelete(Request $request, string $modelName)
    {
        return $this->execute();
    }

    /**
     * Execute a function related with a model.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 07.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicModelFunction(Request $request, string $modelName, string $modelId, string $function)
    {
        return $this->execute();
    }

    /**
     * Public function to execute the `export` method dynamicly.
     * 
     * @param \LaravelDynamicApi\Requests\GenericIndexRequest request The request object.
     * @param string modelClass The class name of the model you want to get the elements.
     * 
     * @return JsonResponse|XmlResponse The JsonResponse or XmlResponse with all elements.
     * 
     * @since 11.09.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicExport(GenericIndexRequest $request, string $modelName)
    {
        return $this->execute();
    }

    /**
     * Generic function to run a function.
     * 
     * @since 07.10.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function dynamicFunction(GenericIndexRequest $request, string $modelName, string $function)
    {
        return $this->execute();
    }
}
