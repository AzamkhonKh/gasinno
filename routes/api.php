<?php

use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
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
    Route::get('/user', function (Request $request) {
        $user = User::where('id', auth()->id())->with(['vehicles'])->first()->append('Roles');
        return $user;
    });
    Route::get('/device/paginate', [DeviceController::class, 'request_geo']);
    Route::get('/device/driver', [DeviceController::class, 'getDeviceDriver']);
    Route::post('/device/turnoff', [DeviceController::class, 'turnOffDriver']);
    Route::get('/device/data', [DeviceController::class, 'deviceData']);
    Route::get('/device/gas', [DeviceController::class, 'gasStatistics']);
    Route::post('/device/register', [RegisterController::class, 'register_device']);
    Route::post('/user/register', [RegisterController::class, 'register']);

    Route::post('/device/assign', [DeviceController::class, 'assign_device']);
    Route::put('/device', [DeviceController::class, 'update']);
    Route::delete('/device/{device_id}', [DeviceController::class, 'destroy']);
    Route::post('/driver/assign', [DriverController::class, 'assign_driver']);
    Route::apiResource('/driver', DriverController::class,['except'=>['index']]);
    Route::get('/user/drivers', [UserController::class, 'getDrivers']);
    Route::get('/user/vehicles', [UserController::class, 'getVehicle']);
});
