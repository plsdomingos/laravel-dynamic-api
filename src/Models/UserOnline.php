<?php

namespace LaravelDynamicApi\Models;

/**
 * Request.
 * 
 * @table requests
 * @belongsTo: User (user)
 * 
 * @since 18.04.2024
 * @author Pedro Domingos <pedro@panttera.com>
 *
 */
class UserOnline extends Model
{
    public $timestamps = false;

    protected $fillable = UserOnline::FIELDS;
    const FIELDS = [
        ...Model::FIELDS,
        'user_id',
        'date'
    ];

    protected $casts = UserOnline::CAST;
    const CAST = [
        ...Model::CAST,
        'date' => 'datetime:Y-m-d',
    ];

    const WITH_FIELDS = [
        ...Model::WITH_FIELDS,
        'user'
    ];
}
