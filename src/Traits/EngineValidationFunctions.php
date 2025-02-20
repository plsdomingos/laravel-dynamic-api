<?php

namespace LaravelDynamicApi\Traits;

use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Engine execution functions
 * 
 * @since 03.03.2023
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait EngineValidationFunctions
{
    /** Validate header.
     * 
     * @param Object $modelClass The model main object.
     * 
     * @throws BadMethodCallException
     * 
     * @since 11.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function validateHeader(): void
    {
        // This validation cause errors during the frontend deploy
        // if (
        //     !(!$this->headerAccept || $this->headerAccept === '' || $this->headerAccept === 'application/xml' || $this->headerAccept === 'application/json' || $this->headerAccept === '*/*')
        // ) {
        //     $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
        //         "Accept key must be filled in the header (application/xml or application/json).";
        // $this->updateRequest(JsonResponse::HTTP_NOT_ACCEPTABLE, $message);
        // $this->saveFaildedRequest();
        //     throw new BadMethodCallException($message);
        // }
        if (!$this->acceptXML && $this->headerAccept === 'application/xml') {
            $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                "This method does not accept XML.";
            $this->updateRequest(JsonResponse::HTTP_NOT_ACCEPTABLE, $message);
            $this->saveFaildedRequest();
            throw new BadMethodCallException($message);
        }
    }

    /** Validate resource permissions per request type and user role.
     * 
     * @param string $type The request type.
     * @param Request $request The request object.
     * 
     * @return void
     * @throws AuthorizationException
     * 
     * @since 31.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function validateExecution(
        string $type = null,
        Request $request = null,
        string $modelClass = null,
        string $modelName = null,
        object $model = null,
        string | null $relationClass = null,
        string | null $relationName = null,
        object | null $relationModel = null,
        string $locale = null,
    ): void {
        // set values
        $type = $type ?? $this->type;
        $request = $request ?? $this->request;
        $modelClass = $modelClass ?? $this->modelClass;
        $modelName = $modelName ?? $this->modelName;
        $model = $model ?? $this->model;
        $relationClass = $relationClass ?? $this->relationClass;
        $relationName = $relationName ?? $this->relationName;
        $relationModel = $relationModel ?? $this->relationModel;
        $locale = $locale ?? $this->locale;

        $allowedModels = config('laravel-dynamic-api.allowed_models', []);
        // If the model is not allowed in general and not allowed particullary, return 403
        if (
            !in_array($this->modelClass, $allowedModels) && !in_array('*', $allowedModels) &&
            !$modelClass::isResourceAllowed($type)
        ) {
            $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                $type . ' method not allowed for model ' . $modelName . '.';
            $this->updateRequest(JsonResponse::HTTP_FORBIDDEN, $message);
            $this->saveFaildedRequest();
            throw new AuthorizationException($message);
        }

        $auth = $modelClass::isAuthRequired($type);
        if ($auth) {
            if ($this->authUser === null) {
                $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'You do not have permission to access to the function ' . $type;
                $this->updateRequest(JsonResponse::HTTP_FORBIDDEN, $message);
                $this->saveFaildedRequest();
                throw new AuthorizationException($message);
            }
            if (!$modelClass::isProfileAllowed($this->type, $this->authUser)) {
                $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'You do not have permission to access to the function ' . $type;
                $this->updateRequest(JsonResponse::HTTP_FORBIDDEN, $message);
                $this->saveFaildedRequest();
                throw new AuthorizationException($message);
            }
        }
        if (!$modelClass::isAllowed(
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
        )) {
            $message = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                'You do not have permission to access this model.';
            $this->updateRequest(JsonResponse::HTTP_FORBIDDEN, $message);
            $this->saveFaildedRequest();
            throw new AuthorizationException($message);
        }
    }
}