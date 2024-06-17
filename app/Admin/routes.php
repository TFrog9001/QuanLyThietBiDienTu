<?php

use Illuminate\Routing\Router;

use App\Admin\Controllers\DeviceExportController;
use Illuminate\Http\Request;
use App\Models\PostOffice;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('districts', DistrictController::class);
    $router->resource('post-offices', PostOfficeController::class);

    $router->resource('device-types', DeviceTypeController::class);
    $router->resource('devices', DeviceController::class);

    $router->resource('device-receipts', DeviceReceiptsController::class);
    $router->resource('device-exports', DeviceExportController::class);

    $router->resource('agencies', AgencyController::class);

});

Route::post('api/post-offices', [DeviceExportController::class, 'getPostOffices']);
Route::get('api/post-offices', [DeviceExportController::class, 'getPostOffices']);
