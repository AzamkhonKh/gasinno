<?php

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\GeoController;
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
Route::any('/test',function (Request $request){
//    $db = parse_url(env('DATABASE_URL'));
    dd($request);
});
Route::any('/send-error',function (Request $request){
//    $db = parse_url(env('DATABASE_URL'));
    return \App\Lib\ApiWrapper::sendResponse(["message" => "TURNOFF"],"TURNOFF");
});
Route::any('/role',function (){
    return \App\Models\Role::all();
});
Route::any('/VehicleData',function (){
    return \App\Models\VehicleData::with(['geo'])->get();
});
Route::any('/users',function (){
    return \App\Models\User::with(['vehicles.geo'])->get();
});
Route::any('/login', [RegisterController::class, 'login']);
Route::any('/register', [RegisterController::class, 'register_device']);
Route::any('/geo-data',[GeoController::class,'get_geo']);

Route::middleware('auth:sanctum')->group(function (){
   Route::any('/user', function (Request $request) {
       $user = User::where('id', auth()->id())->with(['gisData','role'])->first();
       return $user;
   });

});
