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
Route::any('/test', function (Request $request) {
//    $db = parse_url(env('DATABASE_URL'));
    dd($request);
});
Route::any('/send-error', function (Request $request) {
//    $db = parse_url(env('DATABASE_URL'));
    return \App\Lib\ApiWrapper::sendResponse(["message" => "TURNOFF"], "TURNOFF");
});
Route::any('/role', function () {
    return \App\Models\Role::all();
});
Route::any('/VehicleData', function () {
    return \App\Models\VehicleData::with(['geo'])->get();
});
Route::any('/users', function () {
    return \App\Models\User::with(['vehicles.geo'])->get();
});

Route::get('/device/qrcode/{device_id}', [DeviceController::class, 'getQRCode']);

Route::post('/login', [RegisterController::class, 'login']);
Route::get('/geo-data', [GeoController::class, 'get_geo'])->middleware('GeoMiddleware');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = User::where('id', auth()->id())->with(['vehicles'])->first()->append('Roles');
        return $user;
    });
    Route::get('/device', [DeviceController::class, 'request_geo']);
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
