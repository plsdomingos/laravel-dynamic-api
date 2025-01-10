<?php

namespace LaravelDynamicApi\Traits;

use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * Reference Data Trait.
 * 
 * @since 10.01.2025
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait ReferenceDataTrait
{
    use EngineModelFunction;

    /**
     * Cache forever all reference data that are mention in backend-engine
     */
    protected function cacheAllReferenceData(): void
    {
        $routeModels = config('laravel-dynamic-api.dynamic_route_modules', ['*' => '*']);
        foreach ($routeModels as $key => $modelClass) {
            if ($key == '*') {
                continue;
            }
            if (!class_exists($modelClass)) {
                $messageWithLine = __NAMESPACE__ . __CLASS__ . '.' . __FUNCTION__ .  ' [' . __LINE__ . '] ' .
                    'Model Class ' . $modelClass . ' does not exists.';
                throw new Exception($messageWithLine);
            }
            if (!$modelClass::IS_REFERENCE_DATA) {
                continue;
            }
            $this->cacheAndGetReferenceData($key, $modelClass);
        }
    }

    /**
     * Cache and get reference data by model name
     * 
     * @param string $modelName The model name
     */
    protected function cacheAndGetReferenceDataByModelName(string $modelName): mixed
    {
        return $this->cacheAndGetReferenceData($modelName, $this->getModelClass($modelName));
    }

    /**
     * Cache and get reference data by model class
     * 
     * @param string $modelClass The model class
     */
    protected function cacheAndGetReferenceDataByModelClass(string $modelClass): mixed
    {
        return $this->cacheAndGetReferenceData($this->getModelName($modelClass), $modelClass);
    }

    /**
     * Cache and get reference data
     * 
     * @param string $modelName The model name
     * @param string $modelClass The model class
     */
    protected function cacheAndGetReferenceData(string $modelName, string $modelClass): mixed
    {
        return Cache::rememberForever(
            $modelName,
            function () use ($modelClass) {
                // Get all reference data and titles just once.
                return json_decode(json_encode($modelClass::all()));
            }
        );
    }

    /**
     * Refresh and get reference data
     * 
     * @param string $modelName The model name
     * @param string $modelClass The model class
     */
    protected function refreshReferenceData(string $modelName, string $modelClass): mixed
    {
        Cache::forget($modelName);
        return $this->cacheAndGetReferenceData($modelName, $modelClass);
    }

    /**
     * Refresh and get reference data by model class
     * 
     * @param string $modelClass The model class
     */
    protected function refreshReferenceDataByModelClass(string $modelClass): mixed
    {
        return $this->refreshReferenceData($this->getModelName($modelClass), $modelClass);
    }

    /**
     * Refresh and get reference data by model name
     * 
     * @param string $modelName The model name
     */
    protected function refreshReferenceDataByModelName(string $modelName): mixed
    {
        return $this->refreshReferenceData($modelName, $this->getModelClass($modelName));
    }
}
