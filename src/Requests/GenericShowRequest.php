<?php

namespace LaravelDynamicApi\Requests;

use LaravelDynamicApi\Common\Constants;
use Illuminate\Http\Request;

/**
 * Generic Show Request.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class GenericShowRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function rules(Request $request)
    {
        return [
            'output' => 'sometimes|string|in:' . Constants::OUTPUT_SIMPLIFIED . ',' .  Constants::OUTPUT_COMPLETE . ','  . Constants::OUTPUT_EXTENSIVE,
            'show_only' => 'sometimes|array',
            'make_visible' => 'sometimes|array',
            'make_hidden' => 'sometimes|array',
            'with' => 'sometimes|array',
            'with_count' => 'sometimes|array',
            'translations' => $this->getBooleanRule($request, 'translations', 'sometimes'),
        ];
    }
}
