<?php

namespace LaravelDynamicApi\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;


class ValidateModelClass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Allways the singular name
        // $modelClass = Str::singular($request->route()->parameter('modelClass'));
        // $routeModels = config('laravel-dynamic-api.dynamic_route_modules', ['*' => '*']);
        // try {
        //     if (array_key_exists($modelClass, $routeModels)) {
        //         $currentModelClass = $routeModels[$modelClass];
        //         $request->merge(array("modelClass" => $currentModelClass));
        //     }
        //     // All classes are available.
        //     if (array_key_exists('*', $routeModels)) {
        //         $currentModelClass = config('laravel-dynamic-api.models_namespace', 'App\\Models\\') .
        //             Str::singular(Str::replace(' ', '', Str::title(Str::replace('_', ' ', $modelClass))));
        //         $request->merge(array("modelClass" => $currentModelClass));
        //     }
        //     $request->merge(array("modelName" => Str::singular(app($currentModelClass)->getTable())));
        // } catch (Exception $e) {
        //     throw new BadRequestException(
        //         $e->getMessage()
        //     );
        // }

        // foreach (config('laravel-dynamic-api.accept_xml', []) as $accept) {
        //     if ($accept === '*' || $accept === $this->modelClass) {
        //         $this->acceptXML = true;
        //         break;
        //     }
        // }
        // return false;

        return $next($request);
    }
}
