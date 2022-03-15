<?php

use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\UserController;
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
Route::get('/device/qrcode/{device_id}', [DeviceController::class, 'getQRCode']);
Route::post('/login', [RegisterController::class, 'login']);

Route::middleware('GeoMiddleware')->group(function () {
    Route::get('/geo-data', [GeoController::class, 'get_geo']);
    Route::get('/geo-data-supply', [GeoController::class, 'get_supply_geo']);
});

Route::middleware('auth:api')->group(function () {

    Route::group(['prefix' => 'device','middleware' => ['isOwnerOrAdmin']],function(){
        Route::apiResource('/crud', DeviceController::class,['except'=>['index']]);        

        Route::get('/paginate', [DeviceController::class, 'request_geo'])->name('device.paginate');
        Route::get('/paginate-supply', [DeviceController::class, 'paginate_supply']);
        Route::get('/driver', [DeviceController::class, 'getDeviceDriver']);
        Route::get('/gas', [DeviceController::class, 'gasStatistics']);
        Route::get('/gas-supply', [DeviceController::class, 'gasSupplyStatistics']);

        Route::post('/turnoff_on', [DeviceController::class, 'turnOffDevice']);
        
        Route::post('/assign', [DeviceController::class, 'assign_device']);
        Route::get('/getunregisteredDevices', [DeviceController::class, 'getunregisteredDevices'])
                ->middleware('isAdmin');
    });

    Route::group(['prefix' => 'driver'],function(){
        Route::apiResource('/crud', DriverController::class,['except'=>['index']]);
        Route::post('/assign', [DriverController::class, 'assign_driver']);
    });
    
    Route::group(['prefix' => 'user'],function(){
        Route::post('/devices_off_on', [DeviceController::class, 'turnOffUserDevices']);
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/updateData/{id}', [UserController::class, 'updateData'])->middleware('isAdmin');
        Route::get('/drivers', [UserController::class, 'getDrivers']);
        Route::get('/vehicles', [UserController::class, 'getVehicle']);
    });

});
