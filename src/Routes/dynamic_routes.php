<?php

// Actions Handled By Resource Controller
// 'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'

use LaravelDynamicApi\Controllers\DynamicCrudController;
use LaravelDynamicApi\Controllers\DynamicRelationCrudController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// TODO: 'function', 'bulkDestroy'

$dynamicCrud = config('laravel-dynamic-api.dynamic_crud_controller', DynamicCrudController::class);
$dynamicRelationCrud = config('laravel-dynamic-api.dynamic_relation_crud_controller', DynamicRelationCrudController::class);

Route::group(['prefix' => '{modelName}'], function () use ($dynamicCrud, $dynamicRelationCrud) {
    Route::post('exec/{function}', $dynamicCrud . '@dynamicFunction')->name('function')->withoutMiddleware("throttle:api");
    Route::get('export', $dynamicCrud . '@dynamicExport')->name('export');

    Route::group([
        'prefix' => '{modelId}'
    ], function () use ($dynamicCrud, $dynamicRelationCrud) {
        Route::post('exec/{function}', $dynamicCrud . '@dynamicModelFunction')->name('modelFunction')->withoutMiddleware("throttle:api");
        Route::group(['prefix' => '{relationName}'], function () use ($dynamicRelationCrud) {
            Route::group(['prefix' => '{relationModelId}'], function () use ($dynamicRelationCrud) {
                Route::get('{function}', $dynamicRelationCrud . '@dynamicRelationFunction')->name('relationFunction')->withoutMiddleware("throttle:api");
            });
            Route::get('{relationModelId}', $dynamicRelationCrud . '@dynamicRelationShow')->name('relationShow')->withoutMiddleware("throttle:api");
            Route::put('{relationModelId}', $dynamicRelationCrud . '@dynamicRelationUpdate')->name('relationUpdate');
        });
        Route::get('{relationName}', $dynamicRelationCrud . '@dynamicRelationIndex')->name('relationIndex')->withoutMiddleware("throttle:api");
        // Dynamic Bulk
        Route::put('{relationName}', $dynamicRelationCrud . '@dynamicRelationBulkUpdate')->name('relationBulkUpdate');
        Route::post('{relationName}', $dynamicRelationCrud . '@dynamicRelationStore')->name('relationStore');
        Route::delete('{relationName}', $dynamicRelationCrud . '@dynamicRelationDelete')->name('relationBulkDestroy');
    });

    // Dynamic CRUD
    Route::put('{modelId}', $dynamicCrud . '@dynamicUpdate')->name('update');
    Route::delete('{modelId}', $dynamicCrud . '@dynamicDelete')->name('destroy');
    Route::get('{modelId}', $dynamicCrud . '@dynamicShow')->name('show')->withoutMiddleware("throttle:api");
});
Route::get('{modelName}', $dynamicCrud . '@dynamicIndex')->name('index')->withoutMiddleware("throttle:api");
Route::put('{modelName}', $dynamicCrud . '@dynamicBulkUpdate')->name('bulkUpdate');
Route::post('{modelName}', $dynamicCrud . '@dynamicStore')->name('store');
Route::delete('{modelName}', $dynamicCrud . '@dynamicBulkDelete')->name('bulkDestroy');
