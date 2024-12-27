<?php

namespace LaravelDynamicApi\Requests;

use LaravelDynamicApi\Common\Constants;
use Illuminate\Http\Request;

/**
 * Generic Index Request.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class GenericIndexRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @todo Add filter to array
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function rules(Request $request)
    {
        return [
            'output' => 'sometimes|string|in:' . Constants::OUTPUT_SIMPLIFIED . ',' .  Constants::OUTPUT_COMPLETE . ','  . Constants::OUTPUT_EXTENSIVE,
            'term' => 'sometimes|string',
            'paginated' =>  $this->getBooleanRule($request, 'paginated', 'sometimes'),
            'page' => 'sometimes|integer',
            'per_page' => 'sometimes|integer',
            'sort_by' => 'sometimes|string',
            'sort_translation_field_by' => 'sometimes|string',
            'sort_order' => 'sometimes|string|in:asc,desc',
            'show_only' => 'sometimes|array',
            'make_visible' => 'sometimes|array',
            'make_hidden' => 'sometimes|array',
            'with' => 'sometimes|array',
            'with_count' => 'sometimes|array',
            'translations' => $this->getBooleanRule($request, 'translations', 'sometimes'),
            // TODO: 'filter' => 'sometimes|array',
        ];
    }
}
