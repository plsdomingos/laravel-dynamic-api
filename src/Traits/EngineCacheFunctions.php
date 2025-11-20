<?php

namespace LaravelDynamicApi\Traits;

use Illuminate\Support\Facades\Cache;

trait EngineCacheFunctions
{
    /**
     * Get model cache, if does not exists return null.
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     */
    protected function getCache(string $modelClass, string $type, mixed $request): mixed
    {
        if ($modelClass::checkCacheFlag($type)) {
            return Cache::get($this->createCacheKey($modelClass, $type, $request));
        }

        return null;
    }

    /**
     * Get relation cache, if does not exists return null.
     * 
     * @param string $modelClass The model class
     * @param string $relationClass The relation class
     * @param string $type The type
     * @param mixed $request The request
     */
    protected function getRelationCache(
        string $modelClass,
        string $relationClass,
        string $type,
        mixed $request
    ): mixed {
        if ($relationClass::checkCacheFlag($type)) {
            return Cache::get($this->createCacheKey(
                $modelClass . '-' . $relationClass,
                $type,
                $request
            ));
        }

        return null;
    }

    /**
     * Cache and get model
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     * @param mixed $obj The object to save
     */
    protected function saveCache(string $modelClass, string $type, mixed $request, mixed $obj): mixed
    {
        if ($modelClass::checkCacheFlag($type)) {
            return Cache::remember(
                $this->createCacheKey($modelClass, $type, $request),
                config('laravel-dynamic-api.generic_cache_time', 86400),
                function () use ($obj) {
                    return $obj;
                }
            );
        }

        return null;
    }

    /**
     * Cache and get relation
     * 
     * @param string $modelClass The model class
     * @param string $relationClass The relation class
     * @param string $type The type
     * @param mixed $request The request
     * @param mixed $obj The object to save
     */
    protected function saveRelationCache(
        string $modelClass,
        string $relationClass,
        string $type,
        mixed $request,
        mixed $obj
    ): mixed {
        if ($relationClass::checkCacheFlag($type)) {
            return Cache::remember(
                $this->createCacheKey($modelClass . '-' . $relationClass, $type, $request),
                30,
                function () use ($obj) {
                    return $obj;
                }
            );
        }

        return null;
    }

    /**
     * Delete model cache
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     */
    protected function deleteCache(string $modelClass, string $type, mixed $request): void
    {
        if ($modelClass::checkCacheFlag($type)) {
            Cache::forget(
                $this->createCacheKey($modelClass, $type, $request)
            );
        }
    }

    /**
     * Delete relation cache
     * 
     * @param string $modelClass The model class
     * @param string $relationClass The relation class
     * @param string $type The type
     * @param mixed $request The request
     */
    protected function deleteRelationCache(
        string $modelClass,
        string $relationClass,
        string $type,
        mixed $request
    ): void {
        if ($relationClass::checkCacheFlag($type)) {
            Cache::forget(
                $this->createCacheKey($modelClass . '-' . $relationClass, $type, $request)
            );
        }
    }

    /**
     * Create unique cache key
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     * 
     */
    private function createCacheKey(string $modelClass, string $type, mixed $request): string
    {
        return  $type . '::' . $modelClass . '::' .
            sha1(json_encode([
                'path'   => $request->path(),
                'query'  => collect($request->query())->sortKeys()->toArray(),
                'body'   => $request->isMethod('post') ? $request->all() : null,
            ]));
    }
}