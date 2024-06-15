<?php

use Illuminate\Routing\Router;

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

    $router->resource('agencies', AgencyController::class);
});
