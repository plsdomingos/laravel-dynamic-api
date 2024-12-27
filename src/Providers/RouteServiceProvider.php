<?php

namespace LaravelDynamicApi\Providers;

use LaravelDynamicApi\Models\Model;
use LaravelDynamicApi\Traits\RouteServiceProviderTrait;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Route Service Provider.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    use RouteServiceProviderTrait;

    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The route prefix.
     *
     * @var string
     */
    protected $prefix = 'api';

    /**
     * The route midleware.
     *
     * @var array
     */
    protected $middleware = ['api'];

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix($this->prefix)
            ->middleware($this->middleware)
            ->namespace($this->namespace)
            ->group(base_path('vendor/plsdomingos/laravel-dynamic-api/src/Routes/dynamic_routes.php'));
    }

    /**
     * Get Route Models. Override if you want replace, delete or add some class.
     * NOT NEEDED.
     */
    public function getRouteModels(): array
    {
        return collect(File::allFiles(base_path('vendor/plsdomingos/laravel-dynamic-api/src')))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                if (Str::endsWith($path, 'php') && Str::startsWith($path, 'Models')) {
                    $modelName = Str::substr($path, strrpos($path, '/', -1) + 1, strrpos($path, 'php') - strrpos($path, '/', -1) - 2);
                    $class = config('laravel-dynamic-api.models_namespace', 'App\\Models\\') . $modelName;
                    if (class_exists($class)) {
                        if (is_subclass_of($class, Model::class, true)) {
                            return [Str::snake($modelName) => $class];
                        }
                    }
                }
            })
            ->filter(function ($class) {
                return $class !== null;
            })->all();
    }
}