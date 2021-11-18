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
Route::get('/test',function (){
    $db = parse_url(env('DATABASE_URL'));
    print_r($db);
   return 1;
});
Route::get('/role',function (){
   return \App\Models\Role::all();
});
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware('auth:sanctum')->group(function (){
   Route::get('/user', function (Request $request) {
       $user = User::where('id', auth()->id())->with(['gisData','role'])->first();
       return $user;
   });
    Route::post('/geo-data',[GeoController::class,'get_geo']);

});
