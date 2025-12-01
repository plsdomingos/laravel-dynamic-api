<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Requests\GenericIndexRequest;
use LaravelDynamicApi\Requests\GenericShowRequest;
use LaravelDynamicApi\Traits\EngineReturnFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Controller parent class.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class ControllerExecution extends Controller
{
    use EngineReturnFunctions;

    /**
     * Constructor.
     * 
     * @todo App::setLocale($this->locale) is not necessary but is not working. Solve the issue and delete it.
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->setModelClass($request->modelName);
        if (Str::contains($this->type, "relation", true)) {
            $this->setRelationClass($this->relationName);
        }
        if (Str::contains($this->type, "relationOfRelation", true)) {
            $this->setRelationOfRelationClass($this->relationOfRelationName);
        }
        $this->setSpecificModel();
        if (Str::contains($this->type, "relation", true)) {
            $this->setRelationModel($this->relationName, $this->relationModelId);
        }
        if (Str::contains($this->type, "relationOfRelation", true)) {
            $this->setRelationOfRelationModel($this->relationOfRelationName, $this->relationOfRelationModelId);
        }
        $this->validateExecution();
    }

    /** Execute function.
     * 
     * This function do the validation before call the method.
     * 
     * @return JsonResponse|XmlResponse
     * 
     * @since 11.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function execute(): JsonResponse | BinaryFileResponse
    {

        // Egnine Fields for debug
        // return $this->returnOk([
        //     'authUser' => $this->authUser,
        //     'headers' => $this->headers,
        //     'acceptXML' => $this->acceptXML,
        //     'modelName' => $this->modelName,
        //     'modelId' => $this->modelId,
        //     'modelClass' => $this->modelClass,
        //     'model' => $this->model,
        //     'modelTable' => $this->modelTable,
        //     'modelTranslationTable' => $this->modelTranslationTable,
        //     'relationName' => $this->relationName,
        //     'relationModelId' => $this->relationModelId,
        //     'relationClass' => $this->relationClass,
        //     'relationOutput' => $this->relationOutput,
        //     'relationModel' => $this->relationModel,
        //     'relationOfRelationName' => $this->relationOfRelationName,
        //     'relationOfRelationClass' => $this->relationOfRelationClass,
        //     'specificModel' => $this->specificModel,
        //     'relationSpecificModel' => $this->relationSpecificModel,
        //     'relationOfRelationSpecificModel' => $this->relationOfRelationSpecificModel,
        //     'relationBulk' => $this->relationBulk,
        //     'total' => $this->total,
        //     'request' => $this->request->all(),
        //     'data' => $this->data,
        //     'type' => $this->type,
        //     'isFunction' => $this->isFunction,
        //     'function' => $this->function,
        //     'locale' => $this->locale,
        //     'output' => $this->output,
        //     'requestOutput' => $this->requestOutput,
        //     'paginated' => $this->paginated,
        //     'page' => $this->page,
        //     'perPage' => $this->perPage,
        //     'sortOrder' => $this->sortOrder,
        //     'sortBy' => $this->sortBy,
        //     'showOnly' => $this->showOnly,
        //     'makeVisible' => $this->makeVisible,
        //     'makeHidden' => $this->makeHidden,
        //     'with' => $this->with,
        //     'withCount' => $this->withCount,
        //     'term' => $this->term,
        //     'withTranslations' => $this->withTranslations,
        //     'headerAccept' => $this->headerAccept,
        //     'userRequest' => $this->userRequest,
        //     'ip' => $this->ip,
        //     'returnObject' => $this->returnObject,
        // ]);

        try {
            // With XML the valition returns not found for some reason.
            // The same output with report($e)
            // The error needs to be returned manually and the validation must be inside the function.
            $this->data = $this->doValidation();
            $this->executeBeforeFunctions();
            $this->returnObject = $this->doExecute();
            $this->executeAfterFunctions();

            switch ($this->returnType()) {
                case 'export':
                    return (new Collection(array_merge(
                        [$this->modelClass::getExcelHeaders()],
                        $this->modelClass::getExcelExportData(
                            $this->type,
                            $this->request,
                            $this->returnObject,
                            $this->modelClass,
                            $this->modelName,
                            $this->model,
                            $this->relationClass,
                            $this->relationName,
                            $this->relationModel,
                            $this->locale,
                            $this->authUser,
                        )
                    )))->downloadExcel(
                        'test.xlsx',
                        $writerType = null,
                        $headings = false
                    );
                case 'create':
                    return $this->returnCreate($this->returnObject);
                default:
                    return $this->returnOk($this->returnObject);
            }
        } catch (Exception $e) {
            return $this->returnException($e);
        }
    }

    /** 
     * Execute before functions.
     * 
     * The model function returns the respective model. The class function returns the data.
     * 
     * The execution order: 
     * 
     * 1. relation of relation model
     * 2. relation of relation class
     * 3. relation model
     * 4. relation class
     * 5. model
     * 6, model class
     * 7. controller
     * 
     * @param string|null $type API request type. 
     * @param Request|null $request API request.
     * @param array|null $data API data after validation.
     * @param string|null $modelClass The class related with the $modelName from the request.
     * @param string|null $modelName The model name. The first element of the route.
     * @param object|null $model The object getted from the $modelName and the $modelId from the request.
     * @param string|null $relationClass The class related with the $relationName from the request.
     * @param string|null $relationName The relation name. The third element of the route.
     * @param object|null $relationModel The object getted from the $relationName and the $relationModelId from the request.
     * @param string|null $relationOfRelationName The relation of relation name. The fith element of the route..
     * @param object|null $relationOfRelationModel The object getted from the $relationOfRelationName and the $relationOfRelationModelId from the request.
     * @param string $locale Request locale.
     * 
     */
    protected function executeBeforeFunctions(
        ?string $type = null,
        ?Request $request = null,
        ?array $data = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?string $relationOfRelationClass = null,
        ?string $relationOfRelationName = null,
        ?object $relationOfRelationModel = null,
        ?string $locale = null,
    ): void {
        // set values
        $type = $type ?? $this->type;
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
        $relationOfRelationModel = $relationOfRelationModel ?? $this->relationOfRelationModel;
        $locale = $locale ?? $this->locale;

        if ($relationOfRelationClass) {
            if ($relationOfRelationModel) {
                $relationOfRelationModel = $relationOfRelationModel->beforeModelFunction(
                    $type,
                    $request,
                    $data,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $data = $relationOfRelationClass::beforeFunction(
                $type,
                $request,
                $data,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        if ($relationClass) {
            if ($relationModel) {
                $relationModel = $relationModel->beforeModelFunction(
                    $type,
                    $request,
                    $data,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $data = $relationClass::beforeFunction(
                $type,
                $request,
                $data,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        if ($modelClass) {
            if ($model) {
                $model = $model->beforeModelFunction(
                    $type,
                    $request,
                    $data,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $data = $modelClass::beforeFunction(
                $type,
                $request,
                $data,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        // Set Global Variables
        $this->data = $data;
        $this->relationModel = $relationModel;
        $this->model = $model;
        $this->relationOfRelationModel = $relationOfRelationModel;

        $this->beforeFunction();
    }

    /** 
     * Execute after functions.
     * 
     * The model function returns the respective model. The class function returns the data.
     * 
     * The execution order: 
     * 
     * 1. relation of relation model
     * 2. relation of relation class
     * 3. relation model
     * 4. relation class
     * 5. model
     * 6, model class
     * 7. controller
     * 
     * @param string|null $type API request type. 
     * @param Request|null $request API request.
     * @param array|null $data API data after validation.
     * @param string|null $modelClass The class related with the $modelName from the request.
     * @param string|null $modelName The model name. The first element of the route.
     * @param object|null $model The object getted from the $modelName and the $modelId from the request.
     * @param string|null $relationClass The class related with the $relationName from the request.
     * @param string|null $relationName The relation name. The third element of the route.
     * @param object|null $relationModel The object getted from the $relationName and the $relationModelId from the request.
     * @param string|null $relationOfRelationName The relation of relation name. The fith element of the route..
     * @param object|null $relationOfRelationModel The object getted from the $relationOfRelationName and the $relationOfRelationModelId from the request.
     * @param string $locale Request locale.
     * 
     * @return mixed The output to return.
     */
    protected function executeAfterFunctions(
        ?string $type = null,
        ?Request $request = null,
        ?array $data = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?string $relationOfRelationClass = null,
        ?string $relationOfRelationName = null,
        ?object $relationOfRelationModel = null,
        ?string $locale = null,
    ): void {
        // set values
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;
        $data = empty($data) ? $this->data : $data;
        $returnObject = $this->returnObject;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        // TODO: Check when it's a function if we have a problem here.
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $relationOfRelationClass = $relationOfRelationClass ?? $this->relationOfRelationClass;
        $relationOfRelationName = $relationOfRelationName ?? $this->relationOfRelationName;
        $relationOfRelationModel = $relationOfRelationModel ?? $this->relationOfRelationModel;
        $locale = $locale ?? $this->locale;

        if ($relationOfRelationClass) {
            if ($relationOfRelationModel) {
                $returnObject = $relationOfRelationModel->afterModelFunction(
                    $type,
                    $request,
                    $data,
                    $returnObject,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $returnObject = $relationOfRelationClass::afterFunction(
                $type,
                $request,
                $data,
                $returnObject,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        if ($relationClass) {
            if ($relationModel) {
                $returnObject = $relationModel->afterModelFunction(
                    $type,
                    $request,
                    $data,
                    $returnObject,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $returnObject = $relationClass::afterFunction(
                $type,
                $request,
                $data,
                $returnObject,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        if ($modelClass) {
            if ($model) {
                $returnObject = $model->afterModelFunction(
                    $type,
                    $request,
                    $data,
                    $returnObject,
                    $modelClass,
                    $modelName,
                    $model,
                    $relationClass,
                    $relationName,
                    $relationModel,
                    $relationOfRelationClass,
                    $relationOfRelationName,
                    $relationOfRelationModel,
                    $locale,
                    $this->authUser,
                );
            }
            $returnObject = $modelClass::afterFunction(
                $type,
                $request,
                $data,
                $returnObject,
                $modelClass,
                $modelName,
                $model,
                $relationClass,
                $relationName,
                $relationModel,
                $relationOfRelationClass,
                $relationOfRelationName,
                $relationOfRelationModel,
                $locale,
                $this->authUser,
            );
        }

        // Set Global Variables
        $this->returnObject = $returnObject;

        $this->afterFunction();
    }


    /** Validate request by model class, user role and request type.
     * 
     * @param Request $request The request object.
     * @param string $type index The request type.
     * @param bool $modelRulesMandatory If the model rules are mandatory. False by default.
     * 
     * @return array Rules array.
     * @throws UnauthorizedException
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doValidation(
        ?string $type = null,
        ?Request $request = null,
        ?bool $isRelation = null,
        ?bool $modelRulesMandatory = null
    ): array {
        // Set fields.
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;
        $isRelation = $isRelation ?? $this->relationName !== null;
        $modelRulesMandatory = $modelRulesMandatory ?? $this->modelClass::isRulesRequired($type);

        $rules = $this->getRules();
        if ($modelRulesMandatory && ($rules === null || empty($rules))) {
            throw new UnauthorizedException(
                __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'This model does not have rules defined and they are mandatory.'
            );
        }
        $data = $request->all();
        if (empty($rules)) {
            if (!$isRelation) {
                $data = $this->modelClass::normalizeDates($request, $request->all());
            } else if ($this->relationClass !== null) {
                $data = $this->relationClass::normalizeDates($request, $request->all());
            }
            return $data;
        }

        // When the request is XML, to not fail the valdition the request needs to be updated. Examples:
        // If the body is an array, when only one element is sent we need to transform that in one array.
        // The field <myfield/> is an empty array. But in fact can be just a null value.
        // The field <myfield><item>My Item</item></myfield> is a single object, not an array. But in fact can be an array with one element.
        if ($this->acceptXML && $this->headerAccept === 'application/xml') {
            $modelName = $this->modelName;
            if (Str::contains($rules[$modelName], 'array')) {
                $modelName = $this->modelName;
                if (array_key_first($request->$modelName) !== 0 && !is_array($request->$modelName[array_key_first($request->$modelName)])) {
                    $request = new Request([$modelName => [$request[$modelName]]]);
                }
            }
            $newRequest = [];
            foreach ($request->$modelName as $fields) {
                $requestFields = $this->validateElement($fields, $modelName, $rules);
                array_push($newRequest, $requestFields);
            }
            $request = new Request([$modelName => $newRequest]);
        }

        $data = $request->validate($rules);
        if (!$isRelation) {
            $data = $this->modelClass::normalizeDates($request, $data);
        } else {
            $data = $this->relationClass::normalizeDates($request, $data);
        }
        return $data;
    }

    /** Validate element recursive.
     * 
     * @param array $fields The fields array.
     * @param string $modelName The model name.
     * @param array $rules The rules array.
     * @param string $mainKey The main key.
     * 
     * @return array The validated element array.
     * 
     * @since 12.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function validateElement(
        array $fields,
        string $modelName,
        array $rules,
        ?string $mainKey = null
    ): array {
        $requestFields = [];
        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                if (empty($field)) {
                    if (array_key_exists($modelName . '.*.' . $key, $rules)) {
                        if (!Str::contains($rules[$modelName . '.*.' . $key], 'array')) {
                            $type = explode('|', $rules[$modelName . '.*.' . $key])[0];
                            switch ($type) {
                                case 'required':
                                case 'sometimes':
                                case 'nullable':
                                    $requestFields = array_merge($requestFields, [$key => null]);
                                default:
                                    break;
                            }
                            continue;
                        }
                        if (Str::contains($rules[$modelName . '.*.' . $key], 'array')) {
                            $type = explode('|', $rules[$modelName . '.*.' . $key])[0];
                            switch ($type) {
                                case 'nullable':
                                    $requestFields = array_merge($requestFields, [$key => null]);
                                    break;
                                case 'required':
                                case 'sometimes':
                                default:
                                    break;
                            }
                            continue;
                        }
                    }
                }
                if (array_key_first($field) !== 0 && !is_array(reset($field))) {
                    $requestFields = array_merge($requestFields, $this->validateElement($field, $modelName . '.*.' . $key, $rules, $key, true));
                    continue;
                }
            } else {
                if (array_key_exists($modelName . '.*.' . $key, $rules)) {
                    if (Str::contains($rules[$modelName . '.*.' . $key], 'array')) {
                        if (!is_array($field)) {
                            $requestFields = array_merge($requestFields, [$key => [$field]]);
                        }
                        continue;
                    }
                }
            }
            $requestFields = array_merge($requestFields, [$key => $field]);
        }
        if ($mainKey) {
            return [$mainKey => [$requestFields]];
        }
        return $requestFields;
    }

    /** Get rules per type and authenticated user role.
     * 
     * @param string $type The request type.
     * @param Request $request The request object.
     * 
     * @return array With the rules defined in the model, if doesn't exist rules return empty. 
     * 
     * @since 12.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function getRules(
        ?string $type = null,
        ?Request $request = null,
        ?bool $isRelation = null,
        ?string $modelClass = null,
        ?string $modelName = null,
        ?object $model = null,
        ?string $relationClass = null,
        ?string $relationName = null,
        ?object $relationModel = null,
        ?string $locale = null,
        ?string $modelTable = null,
        ?string $modelTranslationTable = null,
    ): array | null {
        // Set fields
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;
        $isRelation = $isRelation ?? $this->relationName !== null;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $locale = $locale ?? $this->locale;
        $modelTable = $modelTable ?? $this->modelTable;
        $modelTranslationTable = $modelTranslationTable ?? $this->modelTranslationTable;

        $rules = [];
        if (array_key_exists($type, $modelClass::VALIDATION_RULES)) {
            if ($this->isFunction) {
                if (!array_key_exists($this->function, $modelClass::VALIDATION_RULES[$type])) {
                    return null;
                }
                $rules = $modelClass::VALIDATION_RULES[$type][$this->function];
            } else if (!$isRelation) {
                $rules = $modelClass::VALIDATION_RULES[$type];
            } else {
                if (array_key_exists($relationName, $modelClass::VALIDATION_RULES[$type])) {
                    $rules = $modelClass::VALIDATION_RULES[$type][$relationName];
                } else if (array_key_exists(Str::lower(Str::replace('relation', '', $type)), $relationClass::VALIDATION_RULES)) {
                    $rules = $relationClass::VALIDATION_RULES[Str::lower(Str::replace('relation', '', $type))];
                }
            }

            foreach ($rules as $field => $rule) {
                if (is_array($rule)) {
                    // Ignore the roles check if the user is super admin
                    if ($this->modelClass::isAuthRequired($type)) {
                        if (!$this->authUser->isSuperAdmin()) {
                            if (array_key_exists('roles', $rule)) {
                                if (!empty($rule['roles'])) {
                                    // If the user is not autheticated and roles is filled, remove this field.
                                    if (!$this->authUser) {
                                        $rules = Arr::except($rules, $field);
                                        continue;
                                    }
                                }
                                // If the user does not contains at least on of the required roles, remove this field.
                                if (!$this->authUser->containsRoles($rule['roles'])) {
                                    $rules = Arr::except($rules, $field);
                                    continue;
                                }
                            }
                        }
                    }

                    $laravelRules = array_key_exists('laravel_rules', $rule) ?
                        (!empty($rule['laravel_rules']) ? array_merge(...collect($rule['laravel_rules'])->map(function ($laravelRule) {
                            return explode('|', $laravelRule);
                        })->all()) : []) :
                        [];

                    $specificRules = [];
                    $modelSpecificRules = [];
                    if (array_key_exists('specific_rules', $rule)) {
                        if (!empty($rule['specific_rules'])) {
                            $specificRules = $this->generateSpecificRules($rule);
                            $modelSpecificRules = $modelClass::generateSpecificRules(
                                $field,
                                $type,
                                $request,
                                $modelClass,
                                $modelName,
                                $model,
                                $relationClass,
                                $relationName,
                                $relationModel,
                                $locale,
                                $this->authUser,
                                $rule,
                                $modelTable,
                                $modelTranslationTable,
                            );
                        }
                    }

                    $rules[$field] = array_merge($laravelRules, $specificRules, $modelSpecificRules);
                    continue;
                }
                $rules[$field] = $rule;
            }
        }
        return $rules ?? null;
    }

    /**
     * Generate specific functions.
     */
    protected function generateSpecificRules(
        array $rule,
        ?string $type = null,
        ?Request $request = null,
    ): array {
        // Set fields
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;

        if (in_array('unique_locale', $rule['specific_rules'])) {
            switch ($type) {
                case 'update':
                    return [Rule::unique($this->modelTranslationTable)
                        ->whereNot(Str::singular($this->modelName) . '_id', $request->id)
                        ->where('locale', $this->locale)];
                case 'store':
                    return [Rule::unique(Str::singular($this->modelName) . '_translations')->where('locale', $this->locale)];
            }
        }

        return [];
    }

    /** Do execute function.
     * 
     * @return Object|array
     * 
     * @since 13.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function doExecute(
        ?string $type = null,
        ?Request $request = null,
        ?array $data = null,
        ?string $modelName = null,
        ?string $modelClass = null,
        ?object $model = null,
        ?string $relationName = null,
        ?string $relationClass = null,
        ?string $function = null,
        ?string $relationModel = null,
        ?string $relationOfRelationClass = null
    ): object {
        // Set fields
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;
        $data = $data ?? $this->data;
        $modelName = $modelName ?? $this->modelName;
        $modelClass = $modelClass ?? $this->modelClass;
        $model = $model ?? $this->model;
        $relationName = $relationName ?? $this->relationName;
        $relationClass = $relationClass ?? $this->relationClass;
        $function = $function ?? $this->function;
        $relationModel = $relationModel ?? $this->relationModel;
        $relationOfRelationClass = $relationOfRelationClass ?? $this->relationOfRelationClass;

        // Execute the function per type.
        switch ($this->type) {
                // CRUD
            case 'index':
            case 'export':
                $output = $this->doIndex($modelClass, new GenericIndexRequest($request->all()), $data);
                break;
            case 'show':
                $output = $this->doShow($model, new GenericShowRequest($request->all()), $data);
                break;
            case 'store':
                $output = $this->doStore($request, $data);
                break;
            case 'update':
                $output = $this->doUpdate($model, $request, $data);
                break;
            case 'destroy':
                $output = $this->doDestroy($model, $request, $data);
                break;
            case 'bulkUpdate':
                $output = $this->doBulkUpdate($modelClass, $request, $data);
                break;
            case 'bulkDestroy':
                $output = $this->doBulkDestroy($modelClass, $request, $data);
                break;

                // CRUD Relations
            case 'relationIndex':
                $output = $this->doRelationIndex($model, new GenericIndexRequest($request->all()), $data, $relationClass);
                break;
            case 'relationShow':
                $output = $this->doRelationShow($model, new GenericShowRequest($request->all()), $data, $relationClass, $relationModel);
                break;
                // TODO
                // case 'relationFunction':
                //     $output = $this->doRelationFunction($model, $request, $data, $relation, $relationModel, $function);
                //     break;
            case 'relationUpdate':
                $output = $this->doRelationUpdate($model, $request, $data, $relationClass, $relationModel);
                break;
            case 'relationStore':
                $output = $this->doRelationStore($model, $request, $data, $relationName);
                break;
            case 'relationBulkDestroy':
                $output = $this->doRelationBulkDestroy($model, $request, $data, $relationName);
                break;
            case 'relationBulkUpdate':
                $output = $this->doRelationBulkUpdate($model, $request, $data, $relationName);
                break;
            case 'relationOfRelationIndex':
                $output = $this->doRelationOfRelationIndex($model, new GenericIndexRequest($request->all()), $data, $relationClass, $relationModel, $relationOfRelationClass);
                break;
                // FUNCTIONS
            case 'modelFunction':
                $output = $this->doModelFunction($model, $request, $data, $function);
                break;
            case 'function':
                $output = $this->doFunction($modelClass, new GenericIndexRequest($request->all()), $data, $function);
                break;
        }
        return $output;
    }
}