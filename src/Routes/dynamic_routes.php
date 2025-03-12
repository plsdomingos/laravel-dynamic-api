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

// Model
Route::post('{modelName}/exec/{function}', $dynamicCrud . '@dynamicFunction')->name('function')->withoutMiddleware("throttle:api");
Route::get('{modelName}/export', $dynamicCrud . '@dynamicExport')->name('export');
Route::post('{modelName}/{modelId}/exec/{function}', $dynamicCrud . '@dynamicModelFunction')->name('modelFunction')->withoutMiddleware("throttle:api");

// Relation
Route::post('{modelName}/{modelId}/{relationName}/{relationModelId}/exec/{function}', $dynamicRelationCrud . '@dynamicRelationFunction')->name('relationFunction')->withoutMiddleware("throttle:api");

// Relation Of Relation
Route::post('{modelName}/{modelId}/{relationName}/{relationModelId}/{relationOfRelationName}/exec/{function}', $dynamicRelationCrud . '@dynamicRelationOfRelationFunction')->name('relationOfRelationFunction')->withoutMiddleware("throttle:api");
Route::get('{modelName}/{modelId}/{relationName}/{relationModelId}/{relationOfRelationName}/{relationOfRelationModelId}', $dynamicRelationCrud . '@dynamicRelationOfRelationShow')->name('relationOfRelationShow')->withoutMiddleware("throttle:api");
Route::put('{modelName}/{modelId}/{relationName}/{relationModelId}/{relationOfRelationName}/{relationOfRelationModelId}', $dynamicRelationCrud . '@dynamicRelationOfRelationUpdate')->name('relationOfRelationUpdate');
Route::post('{modelName}/{modelId}/{relationName}/{relationModelId}/{relationOfRelationName}', $dynamicRelationCrud . '@dynamicRelationOfRelationStore')->name('relationOfRelationStore');
Route::get('{modelName}/{modelId}/{relationName}/{relationModelId}/{relationOfRelationName}', $dynamicRelationCrud . '@dynamicRelationOfRelationIndex')->name('relationOfRelationIndex')->withoutMiddleware("throttle:api");

// Relation
Route::get('{modelName}/{modelId}/{relationName}/{relationModelId}', $dynamicRelationCrud . '@dynamicRelationShow')->name('relationShow')->withoutMiddleware("throttle:api");
Route::put('{modelName}/{modelId}/{relationName}/{relationModelId}', $dynamicRelationCrud . '@dynamicRelationUpdate')->name('relationUpdate');
Route::get('{modelName}/{modelId}/{relationName}', $dynamicRelationCrud . '@dynamicRelationIndex')->name('relationIndex')->withoutMiddleware("throttle:api");
Route::put('{modelName}/{modelId}/{relationName}', $dynamicRelationCrud . '@dynamicRelationBulkUpdate')->name('relationBulkUpdate');
Route::post('{modelName}/{modelId}/{relationName}', $dynamicRelationCrud . '@dynamicRelationStore')->name('relationStore');
Route::delete('{modelName}/{modelId}/{relationName}', $dynamicRelationCrud . '@dynamicRelationDelete')->name('relationBulkDestroy');

// Model
Route::put('{modelName}/{modelId}', $dynamicCrud . '@dynamicUpdate')->name('update');
Route::delete('{modelName}/{modelId}', $dynamicCrud . '@dynamicDelete')->name('destroy');
Route::get('{modelName}/{modelId}', $dynamicCrud . '@dynamicShow')->name('show')->withoutMiddleware("throttle:api");
Route::get('{modelName}', $dynamicCrud . '@dynamicIndex')->name('index')->withoutMiddleware("throttle:api");
Route::put('{modelName}', $dynamicCrud . '@dynamicBulkUpdate')->name('bulkUpdate');
Route::post('{modelName}', $dynamicCrud . '@dynamicStore')->name('store');
Route::delete('{modelName}', $dynamicCrud . '@dynamicBulkDelete')->name('bulkDestroy');
