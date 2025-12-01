<?php

namespace LaravelDynamicApi\Traits;

use App\Jobs\SaveRequest;
use BadMethodCallException;
use LaravelDynamicApi\Common\Constants;
use LaravelDynamicApi\Models\Model;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;

/**
 * Engine return functions
 * 
 * @since 03.03.2023
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait EngineReturnFunctions
{
    /**
     * Return Json or XML Response.
     * 
     * @todo Return XML erro.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnJsonOrXMLResponse(
        $output,
        int $code = JsonResponse::HTTP_OK,
        bool $error = false,
        $modelClass = null
    ): Response | JsonResponse {
        $this->updateRequest($code, $output);
        if ($code > 299) {
            $this->saveFaildedRequest();
        }
        if ($this->acceptXML && $this->headerAccept === 'application/xml') {
            $output = $this->modelClass::updateCDATAFeilds($output, $error);

            if (!is_string($output) && !is_bool($output) && !$error) {
                if (!$output->has($this->modelName)) {
                    $output = collect([$this->modelName => $output->toArray()]);
                }
            } else {
                $output = collect([$this->modelName => $output]);
            }
            return response()->xml(
                $output->toArray(),
                $code,
                [],
                'laravel-dynamic-api',
                'UTF-8'
            );
        }
        return response()->json($output, $code, $this->headers);
    }

    /** Return HTTP OK (200).
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnOk($output): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse($output, JsonResponse::HTTP_OK);
    }

    /** Return HTTP CREATED (201).
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnCreate($output): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse($output, JsonResponse::HTTP_CREATED);
    }

    /** Return HTTP UNAUTHORIZED (401).
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnUnauthorized($message): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse(
            collect([
                'message' => $message,
                'code' => JsonResponse::HTTP_UNAUTHORIZED
            ]),
            JsonResponse::HTTP_UNAUTHORIZED,
            true
        );
    }

    /** Return HTTP FORBIDDEN (403).
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnForbidden($message): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse(
            collect([
                'message' => $message,
                'code' => JsonResponse::HTTP_FORBIDDEN
            ]),
            JsonResponse::HTTP_FORBIDDEN,
            true
        );
    }

    /** Return HTTP BAD REQUEST (400).
     * 
     * @todo Send email or report issue in git when this happen in no debug mode.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnBadRequest($output): Response | JsonResponse
    {
        if (!config('app.debug')) {
            $output = collect(
                ['message' => 'Unexpected Exception. Try later.']
            );
        } else if (is_string($output)) {
            $output = collect(
                ['message' => $output]
            );
        }
        return $this->returnJsonOrXMLResponse($output, JsonResponse::HTTP_BAD_REQUEST, true);
    }

    /** Return HTTP UNPROCESSABLE ENTITY (422).
     * 
     * @since 22.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnUnprocessableEntity(ValidationException $exception): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse(
            collect([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ]),
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            true
        );
    }

    /** Return HTTP HTTP NOT ACCEPTABLE (406).
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnNotAcceptable($message): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse(
            collect([
                'message' => $message,
                'code' => JsonResponse::HTTP_NOT_ACCEPTABLE
            ]),
            JsonResponse::HTTP_NOT_ACCEPTABLE,
            true
        );
    }

    /** Return HTTP HTTP NOT FOUND (404).
     * 
     * @since 26.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnNotFound($message): Response | JsonResponse
    {
        return $this->returnJsonOrXMLResponse(
            collect([
                'message' => $message,
                'code' => JsonResponse::HTTP_NOT_FOUND
            ]),
            JsonResponse::HTTP_NOT_FOUND,
            true
        );
    }

    /** Return excption.
     * 
     * @param Exception $exception The exception object.
     * 
     * @return JsonResponse|XmlResponse
     * 
     * @since 13.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function returnException(Exception $exception): Response | JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->returnUnprocessableEntity($exception);
        }
        if ($exception instanceof UnauthorizedException) {
            return $this->returnUnauthorized($exception->getMessage());
        }
        if ($exception instanceof AuthorizationException) {
            return $this->returnForbidden($exception->getMessage());
        }
        if ($exception instanceof BadMethodCallException) {
            return $this->returnNotAcceptable($exception->getMessage());
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->returnNotFound($exception->getMessage());
        }
        return $this->returnBadRequest([$exception->getMessage(), $exception->getTrace()]);
    }

    /** Abort function.
     * 
     * @param string $errorCode The error code.
     * @param string $message The message to return.
     * 
     * @since 13.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function abort(string $errorCode, string $message = null): never
    {
        switch ($errorCode) {
            case JsonResponse::HTTP_UNAUTHORIZED:
            case 'unauthorized':
                $messageWithLine = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    $message ?? 'You do not have permission to access the requested resource.';
                $this->updateRequest(JsonResponse::HTTP_UNAUTHORIZED, $messageWithLine);
                $this->saveFaildedRequest();
                throw new UnauthorizedException($messageWithLine);
            case JsonResponse::HTTP_FORBIDDEN:
            case 'forbidden':
                $messageWithLine = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    $message ?? 'You do not have permission to access the requested resource.';
                $this->updateRequest(JsonResponse::HTTP_FORBIDDEN, $messageWithLine);
                $this->saveFaildedRequest();
                throw new AuthorizationException($messageWithLine);
            default:
                $messageWithLine = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    $message ?? 'Not described error';
                $this->updateRequest(JsonResponse::HTTP_BAD_REQUEST, $messageWithLine);
                $this->saveFaildedRequest();
                throw new Exception($messageWithLine);
        }
    }

    /**
     * If the type is in the resources array, return false. If the type is a key in the resources
     * array, and the value is a string, and the string is 'auth', return true. If the type is a key in
     * the resources array, and the value is an array, and the array contains 'auth', return true.
     * Otherwise, return false
     * 
     * @param string type The type of resource you're checking.
     * @param resources The array of resources that you want to check against.
     */
    protected function returnType(?string $type = null): string
    {
        if ($type === null) {
            $type = $this->type;
        }
        $value = Model::getExecutionTypeValueByKey($type, $this->modelClass::EXECUTION_TYPES, 'return');
        if ($value !== null) {
            return $value;
        }

        switch ($type) {
            case 'store':
            case 'relationStore':
                return 'create';
            case 'export':
                return 'export';
            default:
                return 'ok';
        }
    }

    /**
     * Get relations visible fields when return the model.
     * 
     * @since 06.11.2023
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function getModelRelationsVisibleFields(
        object $result,
        string $modelClass,
        string $withField,
        string $output = Constants::OUTPUT_SIMPLIFIED
    ) {
        // Transform to array to check the existing keys at this moment.
        $resultArray = array_keys($result->toArray());
        $firstElement = in_array($withField, $resultArray) ? $result->$withField()->first() : null;

        if (!$firstElement) {
            return null;
        }
        $relationClass = get_class($firstElement);

        if (array_key_exists($withField, $modelClass::RELATION_MAKE_VISIBLE_FIELDS)) {
            if (array_key_exists($output, $modelClass::RELATION_MAKE_VISIBLE_FIELDS[$withField])) {
                $alwaysHidden = $relationClass::ALWAYS_HIDDEN;
                if ($this->authUser && $this->authUser->isSuperAdmin()) {
                    $alwaysHidden = [];
                }
                $makeVisible = $modelClass::RELATION_MAKE_VISIBLE_FIELDS[$withField][$output];
                $makeHidden = array_merge(
                    $alwaysHidden,
                    array_values(array_diff(
                        $relationClass::getAllFields(),
                        $makeVisible
                    ))
                );
                return ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden];
            }
        }
        return $this->getVisibleAndHidden($relationClass, $output);
    }

    /** Get visible and hidden fields.
     * 
     * @param string|Object $model The model class name or the model object.
     * @param string $output Kind of output. By default simplified.
     * @param array $makeVisible Make visible fields
     * @param array $makeHidden Make hidden fields
     * @param bool|null $translations Show or not the translations. Null, use the dafult value.
     * @param array $showOnly Show only fields
     * @param array $with With fields.
     * @param array $withCount With count fields.
     *
     * @return array ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden]
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function getVisibleAndHidden(
        $model,
        string $output = Constants::OUTPUT_SIMPLIFIED,
        $originalModel = null,
        array $requestOutput = [],
        array $makeVisible = [],
        array $makeHidden = [],
        ?bool $translations = null,
        array $showOnly = [],
        array $with = [],
        array $withCount = [],
    ): array {
        if ($translations === true) {
            array_push($makeVisible, 'translations');
        } else if ($translations === false) {
            // Ignore
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

        if (!empty($requestOutput)) {
            return $this->requestOutputLogic($model, $this->modelName, $requestOutput, $output);
        }

        if (!empty($showOnly)) {
            return $this->showOnlyLogic($model, $makeHidden, $showOnly, $withCount);
        }

        $makeVisible = array_diff(
            array_merge(
                $model::getVisibleFields($output),
                $makeVisible,
                $with,
                array_map(function ($withCount) {
                    return $withCount . '_count';
                }, array_merge($withCount)),
            ),
            $makeHidden
        );

        $makeHidden = array_values(
            array_diff(
                array_merge(
                    $makeHidden,
                    $model::getHiddenFields($output)
                ),
                $makeVisible
            )
        );

        $withFieldsOutput = [];

        // Hide/show the relation fields.
        foreach ($model::WITH_FIELDS as $withField) {
            if (!in_array($withField, $makeVisible)) {
                continue;
            }

            $relationClass = $this->getModelClass($withField);
            $hasRelationModels = config('laravel-dynamic-api.has_relation_models', null);

            $className = $model;
            if (!is_string($model)) {
                $className = $model::class;
            }
            $tableName = app()->make($className)->getTable();
            if ($hasRelationModels && in_array($tableName . '_' . $withField, $hasRelationModels)) {
                $relationClass = $hasRelationModels[$tableName . '_' . $withField . '_' . $withField];
            }

            if ($originalModel) {
                if (is_string($originalModel)) {
                    if ($originalModel === $relationClass) {
                        if (in_array($withField, $makeVisible)) {
                            $makeVisible = array_diff($makeVisible, [$withField]);
                            array_push($makeHidden, $withField);
                        }
                        continue;
                    }
                } else if ($originalModel::class === $relationClass) {
                    if (in_array($withField, $makeVisible)) {
                        $makeVisible = array_diff($makeVisible, [$withField]);
                        array_push($makeHidden, $withField);
                    }
                    continue;
                }
            }

            $withFieldsOutput = array_merge($withFieldsOutput, $this->getRelationVisibleAndHiddenReturn($model, $withField, $relationClass, $output));
        }

        return array_merge($model::validateVisibleAndHiddenFields($this->authUser, array_values($makeVisible), $makeHidden), $withFieldsOutput);
    }

    /** Show only parameter logic.
     * 
     * @param string|Object $model The model class name or the model object.
     * @param array $makeHidden Make hidden fields
     * @param array $showOnly Show only fields
     * @param array $withCount With count fields.
     * 
     * @return array ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden]
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function showOnlyLogic($model, array $makeHidden, array $showOnly, array $withCount): array
    {
        $makeHidden = array_values(array_diff(
            array_merge(
                $model::getAllFields(),
                $model::getWithFields(Constants::OUTPUT_EXTENSIVE),
                array_map(function ($withCount) {
                    return $withCount . '_count';
                }, array_merge(
                    $model::getWithCountFields(Constants::OUTPUT_EXTENSIVE),
                    $withCount
                )),
                $makeHidden
            ),
            $showOnly
        ));
        $makeVisible = $showOnly;

        return $model::validateVisibleAndHiddenFields($this->authUser, $makeVisible, $makeHidden);
    }

    /** Request Output logic.
     * 
     * @since 10.01.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function requestOutputLogic($model, $modelName, array $requestOutput, $output): array
    {
        $fields = [];
        $withFields = [];

        foreach ($requestOutput as $requestOutputField) {
            if (is_array($requestOutputField)) {
                $relation = $this->requestOutputRelationLogic($requestOutputField, $modelName, $output);
                array_push($fields, array_key_first($relation));
                $withFields = array_merge($withFields, $relation);
                continue;
            }

            if (in_array($requestOutputField, $model::WITH_FIELDS)) {
                array_push($fields, $requestOutputField);
                $relationClass = $this->getModelClass($requestOutputField);

                $hasRelationModels = config('laravel-dynamic-api.has_relation_models', []);
                if (in_array($modelName . '_' . $requestOutputField, array_keys($hasRelationModels))) {
                    $relationClass = $hasRelationModels[$modelName . '_' . $requestOutputField];
                }

                $withFields = array_merge($withFields, $this->getRelationVisibleAndHiddenReturn($model, $requestOutputField, $relationClass, $output));
                continue;
            }

            array_push($fields, $requestOutputField);
        }

        // return [$withFields];
        $counts = array_values(array_diff(array_map(function ($withCount) {
            return $withCount . '_count';
        }, $model::WITH_FIELDS), $fields));

        $makeHidden = array_merge(array_values(array_diff(
            $model::getAllFields(),
            $fields
        )), $counts);
        $makeVisible = $fields;

        $this->with = array_keys($withFields);

        return array_merge($model::validateVisibleAndHiddenFields($this->authUser, $makeVisible, $makeHidden), $withFields);
    }

    /** Request Output, relation logic.
     * 
     * @since 10.01.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function requestOutputRelationLogic($requestOutputField, $modelName, $output): array
    {
        $key = null;
        $keyClass = '';
        $keyFields = [];
        $relationOfRelation = [];

        // Make sure the keyclass already exists before the next foreach
        foreach ($requestOutputField as $requestOutputFieldWith) {
            if (Str::contains($requestOutputFieldWith, '::')) {
                $key = explode('::', $requestOutputFieldWith)[1];
                $keyClass = $this->getModelClass($key);
                $hasRelationModels = config('laravel-dynamic-api.has_relation_models', []);
                if (in_array($modelName . '_' . $key, array_keys($hasRelationModels))) {
                    $keyClass = $hasRelationModels[$modelName . '_' . $key];
                }
                break;
            }
        }

        foreach ($requestOutputField as $requestOutputFieldWith) {
            if (is_array($requestOutputFieldWith)) {
                $relationOfRelationVisibleFields = $this->requestOutputRelationLogic(
                    $requestOutputFieldWith,
                    $modelName,
                    $output
                );
                array_push($keyFields, array_key_first($relationOfRelationVisibleFields));
                $relationOfRelation = array_merge($relationOfRelation, $relationOfRelationVisibleFields);
                continue;
            }

            if (Str::contains($requestOutputFieldWith, '::')) {
                continue;
            }

            if (in_array($requestOutputFieldWith, $keyClass::WITH_FIELDS)) {
                array_push($keyFields, $requestOutputFieldWith);
                $relationClass = $this->getModelClass($requestOutputFieldWith);
                $relationOfRelation = array_merge(
                    $relationOfRelation,
                    $this->getRelationVisibleAndHiddenReturn(
                        $keyClass,
                        $requestOutputFieldWith,
                        $relationClass,
                        $output
                    )
                );
                continue;
            }

            array_push($keyFields, $requestOutputFieldWith);
        }

        $makeHidden = array_values(array_diff(
            $keyClass::getAllFields(),
            $keyFields
        ));

        return [$key => array_merge(['makeVisible' => $keyFields, 'makeHidden' => $makeHidden], $relationOfRelation)];
    }

    /** Get Relation Visibloe and Hidden fields.
     * 
     * @since 10.01.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getRelationVisibleAndHiddenReturn($model, $withField, $relationClass, $output)
    {
        // Hide/show the relation fields.
        if (array_key_exists($withField, $model::RELATION_MAKE_VISIBLE_FIELDS)) {
            if (array_key_exists($output, $model::RELATION_MAKE_VISIBLE_FIELDS[$withField])) {
                $alwaysHidden = $relationClass::ALWAYS_HIDDEN;
                if ($this->authUser && $this->authUser->isSuperAdmin()) {
                    $alwaysHidden = [];
                }
                $withMakeVisible = $model::RELATION_MAKE_VISIBLE_FIELDS[$withField][$output];
                $withMakeHidden = array_merge(
                    $alwaysHidden,
                    array_values(array_diff(
                        $relationClass::getAllFields(),
                        $withMakeVisible
                    ))
                );
                return [$withField => ['makeVisible' => $withMakeVisible, 'makeHidden' => $withMakeHidden]];
            }
        }
        return [$withField => $this->getVisibleAndHidden($relationClass, $output, $model)];
    }

    /** Return the relation formated.
     * 
     * @since 10.01.2024
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     * @todo It's missing to remove the relations of the hasOne or hasMany relations.
     * @todo Add with option in the output
     */
    public function returnRelation($result, $withField, $visibleHidden)
    {
        $relationClass = get_class($result->$withField()->first());
        $relationClassWithFields = array_intersect($relationClass::WITH_FIELDS, $visibleHidden['makeVisible']);
        $withFieldResult = $result->$withField()->with($relationClassWithFields)->get()
            ->makeVisible($visibleHidden['makeVisible'])
            ->makeHidden($visibleHidden['makeHidden']);

        foreach ($withFieldResult as $resultCollection) {
            foreach ($relationClass::WITH_FIELDS as $relationWithField) {
                if (!in_array($relationWithField, $visibleHidden['makeVisible'])) {
                    continue;
                }
                if (!$resultCollection->$relationWithField()->first()) {
                    continue;
                }

                $onlyOne = false;
                if ($resultCollection->$relationWithField() instanceof BelongsTo || $resultCollection->$relationWithField() instanceof HasOne) {
                    $onlyOne = true;
                }
                $withFieldUpdated =  $this->returnRelation($resultCollection, $relationWithField, $visibleHidden[$relationWithField]);

                unset($resultCollection->$relationWithField);
                $resultCollection->$relationWithField = $withFieldUpdated;

                if ($onlyOne) {
                    $resultCollection->$relationWithField = $resultCollection->$relationWithField->first();
                }
            }
        }
        return $withFieldResult;
    }
}