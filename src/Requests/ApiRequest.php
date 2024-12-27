<?php

namespace LaravelDynamicApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Api Request.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class ApiRequest extends FormRequest
{
    /**
     * Get translation model table name from the request.
     *
     * @param $request The API request.
     * @param $path Path of the API before the controller name.
     * @return String
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function getBooleanRule(Request $request, $collection, $require)
    {
        if ($request->has($collection)) {
            if (is_bool($request->$collection)) {
                return $require . '|boolean';
            } else {
                return $require . '|string|in:true,false,1,0,on,off';
            }
        }

        return $require . '|string|in:true,false,1,0,on,off';
    }
}
