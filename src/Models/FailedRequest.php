<?php

namespace LaravelDynamicApi\Models;

/**
 * Failed Requests.
 * 
 * @table failed_requests
 * 
 * @since 26.03.2024
 * @author Pedro Domingos <pedro@panttera.com>
 *
 */
class FailedRequest extends Model
{
    public $timestamps = false;
    protected $fillable = FailedRequest::FIELDS;
    const FIELDS = [
        ...Model::FIELDS,
        'request_id',
        'accept_xml',
        'model_class',
        'model',
        'model_table',
        'model_translation_table',
        'relation_class',
        'relation_output',
        'relation_model',
        'specific_model',
        'relation_specific_model',
        'relation_bulk',
        'total',
        'data',
        'is_function',
        'function',
        'locale',
        'output',
        'request_output',
        'paginated',
        'page',
        'per_page',
        'sort_order',
        'sort_by',
        'show_only',
        'make_visible',
        'makehidden',
        'with',
        'with_count',
        'term',
        'with_translations',
        'header_accept',
        'ips',
        'content_types',
        'data',
        'return_object',
    ];

    protected $casts = FailedRequest::CAST;
    const CAST = [
        ...Model::CAST,
        'data' => 'array',
        'request_output' => 'array',
        'return_object' => 'array',
        'show_only' => 'array',
        'make_visible' => 'array',
        'makehidden' => 'array',
        'ips' => 'array',
        'content_types' => 'array',
        'with' => 'array',
        'with_count' => 'array',
        'accept_xml' => 'boolean',
        'specific_model' => 'boolean',
        'relation_specific_model' => 'boolean',
        'relation_bulk' => 'boolean',
        'is_function' => 'boolean',
        'with_translations' => 'boolean',
    ];
}
