<?php

namespace LaravelDynamicApi\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Engine return functions
 * 
 * @since 20.01.2025
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait EngineModelFunction
{
    /** Get model class from model name.
     * 
     * @param string $modelName Model Name.
     * 
     * @return string
     */
    protected function getModelClass(string $modelName = null): string
    {
        $modelName = $modelName ?? $this->modelName;
        $modelName = Str::plural($modelName);

        try {
            // Check if the model is configured
            $routeModels = config('laravel-dynamic-api.dynamic_route_modules', ['*' => '*']);
            if (array_key_exists($modelName, $routeModels)) {
                $modelClass = $routeModels[$modelName];

                if (!class_exists($modelClass)) {
                    throw new Exception;
                }
                return $modelClass;
            }
            // All classes are available.
            if (array_key_exists('*', $routeModels)) {
                $modelClass = config('laravel-dynamic-api.models_namespace', 'App\\Models\\') .
                    Str::singular(Str::replace(' ', '', Str::title(Str::replace('_', ' ', $modelName))));

                if (!class_exists($modelClass)) {
                    throw new Exception;
                }

                return $modelClass;
            }
        } catch (Exception $e) {
            throw new ModelNotFoundException(
                __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'Not found model class ' . $modelName . '. '
            );
        }
    }

    /** Get model name from model class.
     * 
     * @param string $modelClass Model class.
     * 
     * @return string
     */
    protected function getModelName(string $modelClass = null): string
    {
        $modelClass = $modelClass ?? $this->modelClass;

        try {
            // Check if the model is configured
            $routeModels = config('laravel-dynamic-api.dynamic_route_modules', ['*' => '*']);
            $modelName = array_search($modelClass, $routeModels);

            if (!$modelName) {
                // All classes are available.
                if (array_key_exists('*', $routeModels)) {
                    $modelName = app()->make($modelClass)->getTable();
                }
            }
            return $modelName;
        } catch (Exception $e) {
            throw new ModelNotFoundException(
                __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'Not found model class ' . $modelClass . '. '
            );
        }
    }
}
