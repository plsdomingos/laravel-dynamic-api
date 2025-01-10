<?php

namespace LaravelDynamicApi\Controllers;

use LaravelDynamicApi\Common\Constants;
use LaravelDynamicApi\Traits\EngineModelFunction;
use LaravelDynamicApi\Traits\EngineRequestFunctions;
use LaravelDynamicApi\Traits\EngineReturnFunctions;
use LaravelDynamicApi\Traits\EngineValidationFunctions;
use LaravelDynamicApi\Traits\RouteServiceProviderTrait;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/** Controller parent class.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests,
        RouteServiceProviderTrait,
        EngineReturnFunctions,
        EngineValidationFunctions,
        EngineRequestFunctions,
        EngineModelFunction;

    /** @var object $authUser Authenticaded user. */
    protected $authUser;

    /** @var array $headers Output headers to include in the response. */
    protected $headers = [];

    /** @var boolean $acceptXML If the model accepts XML requests. Can be configured in backend-engine.php or inside the model. */
    protected $acceptXML = false;

    /** @var string $modelClass The class related with the $modelName from the request. */
    protected $modelClass;

    /** @var object $model The object getted from the $modelName and the $modelId from the request.  */
    protected $model;

    /** @var string $modelTable The table associated to the $modelName and the $modelId from the request.  */
    protected $modelTable;

    /** @var string $modelTranslationTable The translation table associated to the $modelName and the $modelId from the request.  */
    protected $modelTranslationTable;

    /** @var string $relationClass The class related with the $relationName from the request. */
    protected $relationClass;

    /** @var object $relationOutput All elements of a relation. */
    protected $relationOutput;

    /** @var object $relationModel The relation model. */
    protected $relationModel;

    /** @var bool $specificModel True if the execution runs to a specific model, for example show, false if not. */
    protected bool $specificModel = false;

    /** @var bool $relationSpecificModel True if the execution runs to a relation specific model, for example relationUpdate, false if not */
    protected bool $relationSpecificModel = false;

    /** @var bool $relationBulk True if the execution runs to a relation for multiple models, for example relationBulkUpdate, false if not */
    protected bool $relationBulk = false;

    /** @var int $total Total elements. */
    protected $total = 0;

    /******** REQUEST VARIABLES ************/

    /** @var Request $request API request. */
    protected $request;

    /** @var string $type API request type. Get from the route name. */
    protected $type;

    /** @var string $modelName The model name. The first element of the route. */
    protected $modelName;

    /** @var string $modelId The model identifier. The second element of the route. */
    protected $modelId;

    /** @var string $relationName The relation name. The third element of the route. */
    protected $relationName;

    /** @var string $relationModelId The relation model identifier. The fourth element of the route. */
    protected $relationModelId;

    /** @var boolean $isFunction If the call is a fnction. */
    protected $isFunction;

    /** @var string $function API function. */
    protected $function;

    /** @var string $locale Request locale. German by default. */
    protected $locale;

    /** @var string $output The output type. */
    protected $output;

    /** @var array $request_output The desired output.
     * This field will ignore the fields $output, $with, $withCount, $makeVisible, $makeHidden, $showOnly. 
     */
    protected $requestOutput;

    /** @var bool $paginated If the output is paginated or not. */
    protected $paginated;

    /** @var int $page Requested page for paginated outputs. */
    protected $page;

    /** @var int $perPage Total elements per page for paginated outputs. */
    protected $perPage;

    /** @var string $sortOrder Sort order. 'asc' or 'desc' */
    protected $sortOrder;

    /** @var string $sortBy Output sort field. */
    protected $sortBy;

    /** @var string $sortByRaw Output sort by raw fields. */
    protected $sortByRaw;

    /** @var array $showOnly Only the fields in this variable will be returned.
     * If this field is sent the fields $makeVisible, $makeHidden, $with and $withCount will be ignored.
     */
    protected $showOnly;

    /** @var array $makeVisible Make visible additional fields to the output. */
    protected $makeVisible;

    /** @var array $makeHidden Hide fields to the output. */
    protected $makeHidden;

    /** @var array $with Add relation elements to the output. */
    protected $with;

    /** @var array $withCount Add a total of the related elements to the output. */
    protected $withCount;

    /** @var string $term Term to filter the output. */
    protected $term;

    /** @var bool $withTranslations If true, the translations are returned in all types of requests, if false no. */
    protected $withTranslations = false;

    /** @var string $headerAccept Value of the 'Accept' key from the request header. */
    protected $headerAccept;

    /** @var object $userRequest user request saved in the database. */
    protected $userRequest;

    /** @var string $ip Request IP. */
    protected $ip;

    /** @var array $data API data. */
    protected $data;

    /** @var mixed $returnObject Return object. */
    protected $returnObject;

    /**
     * Constructor.
     * 
     * @todo App::setLocale($this->locale) is not necessary but is not working. Solve the issue and delete it.
     */
    public function __construct(Request $request)
    {
        $this->saveRequest($request);

        $type = Route::currentRouteName();
        $this->request = $request;
        $this->type = $type;
        $this->modelName = $request->modelName;
        $this->modelId = $request->modelId;
        $this->relationName = $request->relationName;
        $this->relationModelId = $request->relationModelId;
        $this->isFunction = $type === 'modelFunction' || $type === 'function';
        $this->function = $request->function ?? null;
        $this->requestOutput = $request->request_output ?
            (is_array($request->request_output) ? $request->request_output : [$request->request_output]) : [];
        $this->paginated = $request->paginated ? $this->convertBooleans($request->paginated) : null;
        $this->page = $request->page ?? 1;
        $this->perPage = $request->per_page ?? 10;
        $this->sortOrder = $request->sort_order ??
            ($request->sort && is_array($request->sort) && count($request->sort) == 2 ? $request->sort[1] : 'asc');
        $this->sortBy = $request->sort_by ??
            ($request->sort && is_array($request->sort) && count($request->sort) == 2 ? $request->sort[0] : 'id');
        $this->showOnly = $request->show_only ?
            (is_array($request->show_only) ? $request->show_only : [$request->show_only]) : [];
        $this->makeVisible = $request->make_visible ?
            (is_array($request->make_visible) ? $request->make_visible : [$request->make_visible]) : [];
        $this->makeHidden = $request->make_hidden ?
            (is_array($request->make_hidden) ? $request->make_hidden : [$request->make_hidden]) : [];
        $this->with = $request->with ?
            (is_array($request->with) ? $request->with : [$request->with]) : [];
        $this->withCount = $request->with_count ?
            (is_array($request->with_count) ? $request->with_count : [$request->with_count]) : [];
        $this->term = $request->term ?? null;
        $this->withTranslations = $request->withTranslations ?
            $this->convertBooleans($request->withTranslations) : null;
        $this->ip = $request->ip();
        $this->headerAccept = $request->header('Accept');
        $this->locale = $request->has('locale') ?
            $request->input('locale') ?? config('translatable.fallback_locale')
            : config('translatable.fallback_locale');

        // TODO: This is not needed, but fail if we don't use it.
        App::setLocale($this->locale);
        // Add global variables and validations
        $this->setAuthUser();
        $this->setOutput();
        $this->setAcceptXml();
        $this->validateHeader();
    }

    protected function setOutput(Request $request = null, string $type = null): void
    {
        if ($type === null) {
            $type = $this->type;
        }
        if ($request === null) {
            $request = $this->request;
        }

        $this->output = $request->output ?? Constants::OUTPUT_SIMPLIFIED;

        switch ($type) {
            case 'relationShow':
            case 'relationFunction':
            case 'relationUpdate':
            case 'relationDestroy':
                $this->relationSpecificModel = true;
            case 'show':
            case 'destroy':
            case 'update':
            case 'relationStore':
            case 'relationBulkUpdate':
            case 'relationBulkDestroy':
            case 'modelFunction':
                $this->specificModel = true;
            case 'store':
            case 'relationIndex':
                $this->output = $this->request->output ?? Constants::OUTPUT_COMPLETE;
                break;
        }
    }

    /** Set auth user into the global variables.
     * 
     * @return void
     */
    protected function setAuthUser(Request $request = null): void
    {
        $request = $request ?? $this->request;
        // Get auth user from passport
        $this->authUser = $request->user('api');
        if (!$this->authUser) {
            $this->authUser = Auth::user() ? Auth::user() : null;
            if (!$this->authUser) {
                // Get auth user from sanctum
                $this->authUser = auth('sanctum')->user();
            }
        }
        if ($this->authUser) {
            $this->authUser->updateLastLogin();
            Auth::login($this->authUser);
        }
        $this->updateRequest();
    }

    /** Set aceept xml into the global variables.
     * 
     * @return void
     */
    protected function setAcceptXml(): void
    {
        foreach (config('laravel-dynamic-api.accept_xml', []) as $accept) {
            if ($accept === '*' || $accept === $this->modelName) {
                $this->acceptXML = true;
                break;
            }
        }
    }

    /** Set model class into the global variables.
     * 
     * @param string $modelClass Model class.
     * 
     * @return bool If false means the model class doesn't exist.
     */
    protected function setModelClass(string $modelName = null): void
    {
        $modelName = $modelName ?? $this->modelName;

        try {
            // Check if the model is configured
            $routeModels = config('laravel-dynamic-api.dynamic_route_modules', ['*' => '*']);
            if (array_key_exists($modelName, $routeModels)) {
                $this->modelClass = $routeModels[$modelName];

                if (!class_exists($this->modelClass)) {
                    throw new Exception;
                }

                $this->modelTable = app()->make($this->modelClass)->getTable();
                try {
                    $this->modelTranslationTable = app()->make(app()->make($this->modelClass)->getTranslationModelName())->getTable();
                } catch (Exception $e) {
                    $this->modelTranslationTable = null;
                }

                return;
            }
            // All classes are available.
            if (array_key_exists('*', $routeModels)) {
                $this->modelClass = config('laravel-dynamic-api.models_namespace', 'App\\Models\\') .
                    Str::singular(Str::replace(' ', '', Str::title(Str::replace('_', ' ', $modelName))));

                if (!class_exists($this->modelClass)) {
                    throw new Exception;
                }

                $this->modelTable = app()->make($this->modelClass)->getTable();
                try {
                    $this->modelTranslationTable = app()->make(app()->make($this->modelClass)->getTranslationModelName())->getTable();
                } catch (Exception $e) {
                    $this->modelTranslationTable = null;
                }

                return;
            }
        } catch (Exception $e) {
            throw new BadRequestException(
                __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'Model class ' . $modelName . ' does not exist. '
            );
        }
    }

    /** Set rlatione class into the global variables.
     * 
     * @param string $relationClass Model class.
     * 
     * @return bool If false means the model class doesn't exist.
     */
    protected function setRelationClass(string $relationName = null): void
    {
        $relationName = $relationName ?? $this->relationName;
        $this->relationClass = $this->getModelClass($relationName);
    }

    /** Set model class into the global variables.
     * 
     * @param string $modelClass Model class.
     * 
     * @return bool If false means the model class doesn't exist.
     */
    protected function setSpecificModel(
        string $modelClass = null,
        string $modelId = null,
        string $locale = null,
        string $output = null,
        string $withCount = null,
        string $with = null,
    ): void {
        $modelClass = $modelClass ?? $this->modelClass;
        $modelId = $modelId ?? $this->modelId;
        $locale = $locale ?? $this->locale;
        $output = $output ?? $this->output;
        $withCount = $withCount ?? $this->withCount;
        $with = $with ?? $this->with;

        switch ($this->type) {
            case 'relationShow':
            case 'relationFunction':
            case 'relationUpdate':
            case 'relationDestroy':
                $this->relationSpecificModel = true;
            case 'show':
            case 'destroy':
            case 'update':
            case 'modelFunction':
                $this->specificModel = true;
            case 'relationIndex':
            case 'relationBulkUpdate':
            case 'relationBulkDestroy':
            case 'relationStore':
            case 'relationImport':
                $this->model = $this->resolveModelTrait(
                    $this->userRequest,
                    $this->modelClass,
                    $this->modelId,
                    $this->locale,
                    $this->output,
                    $this->withCount,
                    $this->with,
                    in_array('slug', $this->modelClass::TRANSLATED_FIELDS)
                );
                break;
        }

        switch ($this->type) {
            case 'relationBulkUpdate':
            case 'relationBulkDestroy':
                $this->relationBulk = true;
                break;
        }
    }

    protected function setRelationModel(
        string $relationName = null,
        string $relationModelId = null,
        string $locale = null,
    ): void {
        $relationName = $relationName ?? $this->relationName;
        $relationModelId = $relationModelId ?? $this->relationModelId;
        $locale = $locale ?? $this->locale;

        try {
            $this->relationOutput = $this->model->$relationName()
                ->with($this->relationClass::WITH_FIELDS)
                ->withTrashed()->get();
        } catch (Exception $e) {
            $this->relationOutput = $this->model->$relationName;
        } finally {
            if ($this->relationOutput === null) {
                throw new BadRequestException(
                    __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                        "The model " . $this->model::class . " does not contains the relation $relationName."
                );
            }
        }

        if ($this->relationSpecificModel) {
            $this->relationModel = $this->resolveRelationModelTrait(
                $this->userRequest,
                $this->relationOutput,
                $relationModelId,
                $locale,
                in_array('slug', $this->modelClass::TRANSLATED_FIELDS)
            );
        }
    }

    /** Function to run before the execution.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the controller, if neessary.
     * 
     * @param Request $request The request object.
     * @param array $data The request data array.
     * @param string|object $model Model or model class.
     * @param string $type The request type.
     * 
     * 
     * @since 10.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function beforeFunction(): void {}

    /** Function to run after the execution.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the controller, if neessary.
     * 
     * @param Request $request The request object.
     * @param array $data The request data array.
     * @param string|object $model Model or model class.
     * @param string $type The request type.
     * 
     * 
     * @since 10.06.2022
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    protected function afterFunction(): void {}
}