<?php

namespace LaravelDynamicApi\Models;

use App\Common\Constants;

/**
 * Request.
 * 
 * @table requests
 * @belongsTo: User (user)
 * 
 * @since 25.03.2024
 * @author Pedro Domingos <pedro@panttera.com>
 *
 */
class Request extends Model
{
    protected $fillable = Request::FIELDS;
    const FIELDS = [
        ...Model::FIELDS,
        'track_id',
        'user_id',
        'method',
        'path',
        'schema',
        'query',
        'headers',
        'ip',
        'request',
        'status',
        'type',
        'model_name',
        'model_id',
        'relation_name',
        'relation_model_id',
        'return_output',
        'created_at',
        'updated_at'
    ];

    protected $casts = Request::CAST;
    const CAST = [
        ...Model::CAST,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'query' => 'array',
        'request' => 'array',
        'headers' => 'array',
        'return_output' => 'array',
    ];

    const WITH_FIELDS = [
        ...Model::WITH_FIELDS,
        'user'
    ];

    const IGNORE_FILTERS = [
        ...Model::IGNORE_FILTERS,
        'include_requests',
        'only_failed',
        'remove_get'
    ];

    const SIMPLIFIED_FIELDS = [
        ...Model::SIMPLIFIED_FIELDS,
        'method',
        'path',
        'schema',
        'status',
        'created_at'
    ];

    public static function collectionFilter(
        mixed $query,
        mixed $pivot,
        mixed $filter,
        string | null $term,
        array $ignoreFilters,
        array $termFilters,
        array $relationIgnoreFilters,
        array $relationTermFilters,
        array $relationOfRelationIgnoreFilter,
        array $relationOfRelationTermFilters,
        string $modelClass,
        string $modelName,
        object $model,
        string $relationClass,
        string $relationName,
        object | null $relationModel,
        object | null $relationOfRelationClass,
        object | null $relationOfRelationName,
        mixed  $sortBy,
        mixed  $sortOrder,
        mixed  $sortByRaw,
        array  $ignoreSort,
        array $relationIgnoreSort,
        array $relationOfRelationIgnoreSort,
        int $page,
        int $perPage,
        object | null $authUser,
    ): mixed {
        $includeRequests = false;
        $removeGet = false;
        $onlyFailed = false;

        if ($filter) {
            foreach ($filter as $key => $val) {
                switch ($key) {
                    case 'include_requests':
                        $includeRequests = $val === true ? true : false;
                        break;
                    case 'remove_get':
                        $removeGet = $val === true ? true : false;
                        break;
                    case 'only_failed':
                        $onlyFailed = $val === true ? true : false;
                        break;
                    default:
                        break;
                }
            }
        }
        if (!$includeRequests) {
            $query = $query->filter(function ($q) {
                return $q['relation_name'] !== 'requests';
            });
        }
        if ($removeGet) {
            $query = $query->filter(function ($q) {
                return $q['method'] !== 'GET';
            });
        }
        if ($onlyFailed) {
            $query = $query->filter(function ($q) {
                return $q['status'] > 200 || $q['status'] === null;
            });
        }

        return $query;
    }

    public function user()
    {
        $modules = config('laravel-dynamic-api.dynamic_route_modules', null);
        if ($modules && array_key_exists('users', $modules)) {
            return $this->belongsTo($modules['users']);
        }
        return $this->belongsTo(config('laravel-dynamic-api.models_namespace', '') . 'User');
    }
}