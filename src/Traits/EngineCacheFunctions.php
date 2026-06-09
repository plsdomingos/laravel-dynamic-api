<?php

namespace LaravelDynamicApi\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait EngineCacheFunctions
{
    /**
     * Get model cache, if does not exists return null.
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     */
    protected function getCache(string $modelClass, string $type, mixed $request, ?int $modelId = null): mixed
    {
        if ($modelClass::checkCacheFlag($type)) {
            $cahedValue = Cache::get($this->createCacheKey($modelClass, $type, $request));
            $cahedValue = is_string($cahedValue) ? json_decode($cahedValue, true) : $cahedValue;

            return is_array($cahedValue) ? collect($cahedValue) : $cahedValue;
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
        mixed $request,
        int $modelId,
        ?int $relationId = null
    ): mixed {
        if ($relationClass::checkCacheFlag($type)) {
            return Cache::get($this->createRelationCacheKey(
                $modelClass,
                $relationClass,
                $type,
                $request,
                $modelId,
                $relationId
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
     * 
     */
    protected function saveCache(
        string $modelClass,
        string $type,
        mixed $request,
        mixed $obj,
        ?int $modelId = null
    ): mixed {
        $cacheKey = $this->createCacheKey($modelClass, $type, $request, $modelId);

        file_put_contents(
            storage_path('framework/cache/data/keys.txt'),
            $cacheKey . PHP_EOL,
            FILE_APPEND
        );

        if ($modelClass::checkCacheFlag($type)) {
            return Cache::remember(
                $cacheKey,
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
        mixed $obj,
        int $modelId,
        ?int $relationId = null
    ): mixed {
        $cacheKey = $this->createRelationCacheKey(
            $modelClass,
            $relationClass,
            $type,
            $request,
            $modelId,
            $relationId
        );

        file_put_contents(
            storage_path('framework/cache/data/keys.txt'),
            $cacheKey . PHP_EOL,
            FILE_APPEND
        );

        if ($relationClass::checkCacheFlag($type)) {
            return Cache::remember(
                $cacheKey,
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
    protected function deleteCache(
        string $modelClass,
        string $type,
        mixed $request,
        ?int $modelId = null
    ): void {
        if ($modelClass::checkCacheFlag($type)) {
            $keysFilePath = storage_path('framework/cache/data/keys.txt');
            $keysToDelete = [];
            if (file_exists($keysFilePath)) {
                foreach (file($keysFilePath) as $line) {
                    if ($modelId == null) {
                        if (Str::contains($line, $modelClass)) {
                            $keysToDelete[] = trim($line);
                        }
                    } else {
                        if (
                            Str::contains($line, $modelClass . '::index') ||
                            Str::contains($line, $modelClass . '::' .  $modelId)
                        ) {
                            $keysToDelete[] = trim($line);
                        }
                    }
                }
            }
            foreach ($keysToDelete as $key) {
                $contents = file_get_contents($keysFilePath);
                $contents = str_replace($key, '', $contents);
                file_put_contents($keysFilePath, $contents);
                Cache::forget($key);
            }
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
        mixed $request,
        int $modelId,
        ?int $relationlId = null
    ): void {
        if ($relationClass::checkCacheFlag($type)) {
            $keysFilePath = storage_path('framework/cache/data/keys.txt');
            $keysToDelete = [];
            if (file_exists($keysFilePath)) {
                foreach (file($keysFilePath) as $line) {
                    if ($relationlId == null) {
                        if (Str::contains($line, $relationClass)) {
                            $keysToDelete[] = trim($line);
                        }
                    } else {
                        if (
                            Str::contains($line, $relationClass . '::index') ||
                            Str::contains($line, $relationClass . '::' .  $relationlId)
                        ) {
                            $keysToDelete[] = trim($line);
                        }
                    }
                }
            }
            foreach ($keysToDelete as $key) {
                $contents = file_get_contents($keysFilePath);
                $contents = str_replace($key, '', $contents);
                file_put_contents($keysFilePath, $contents);
                Cache::forget($key);
            }
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
    private function createCacheKey(string $modelClass, string $type, mixed $request, ?int $modelId = null): string
    {
        $keyPrefix = $modelClass . '::';

        if ($modelId) {
            $keyPrefix .= $modelId . '::';
        }

        return  $keyPrefix . $type . '::' .
            sha1(json_encode([
                'path'   => $request->path(),
                'query'  => collect($request->query())->sortKeys()->toArray(),
                'body'   => $request->isMethod('post') ? $request->all() : null,
            ]));
    }

    /**
     * Create unique cache key
     * 
     * @param string $modelClass The model class
     * @param string $type The type
     * @param mixed $request The request
     * 
     */
    private function createRelationCacheKey(
        string $modelClass,
        string $relationClass,
        string $type,
        mixed $request,
        int $modelId,
        ?int $relationId = null
    ): string {
        $keyPrefix = $modelClass . '::' .  $modelId . '::' . $relationClass . '::';

        if ($relationId) {
            $keyPrefix .= $relationId . '::';
        }

        return  $keyPrefix . $type . '::' .
            sha1(json_encode([
                'path'   => $request->path(),
                'query'  => collect($request->query())->sortKeys()->toArray(),
                'body'   => $request->isMethod('post') ? $request->all() : null,
            ]));
    }
}