<?php

namespace LaravelDynamicApi\Providers;

use LaravelDynamicApi\Traits\ReferenceDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use ReferenceDataTrait;
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        // Cache all reference data in backend-engine -> dynamic_route_modules
        $this->cacheAllReferenceData();
    }
}
