<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\RegisterRequest;
use App\Http\Requests\RegisterDeviceRequest;
use App\Lib\ApiWrapper;
use App\Models\IntegrationLog;
use App\Models\IPData;
use App\Models\User;
use App\Models\VehicleData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\DriverCarRelation;

class RegisterController extends Controller
{
    public function register_device(RegisterDeviceRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $input = $request->validated();
            if (!isset($input['car_number'])) {
                $input['car_number'] = Str::random(10);
            }
            if (!isset($input['balloon_volume'])) {
                $input['balloon_volume'] = 1;
            }
            if (!isset($input['owner_id'])) {
                $input['owner_id'] = auth()->id();
            }
            
            $token = Str::random(80);
            $input['token'] = Hash::make($token);
            $car = VehicleData::create($input);
            
            if(isset($data['driver_id'])){
                DriverCarRelation::query()->updateOrCreate(
                    [
                        'vehicle_id' => $car->id
                    ],
                    [
                        'driver_id' => $data['driver_id']
                    ]
                );
            }

            $success['token'] = $token;
            $success['device_id'] = $car->id;
            $res = $success;
            $msg = "SUCCESS";
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        IntegrationLog::log($request, [$res, $msg]);
        return ApiWrapper::sendResponse($res, $msg);
    }

    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        $request->request->add(['mac' => User::getMac()]);
        IPData::log($request);
        try {
            $input = $request->all();
            $input['api_token'] = Str::random(50);
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);
            $user->save();
            $success['token'] = $user->api_token;
            $success['name'] = $user->name;
            $res = $success;
            $msg = "SUCCESS";
        } catch (\Exception $e) {
            DB::rollBack();
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        DB::commit();
        IntegrationLog::log($request, [$res, $msg]);
        return ApiWrapper::sendResponse($res, $msg);
    }

    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $credentials = array(
            'phone' => $request->input('phone'),
            'password' => $request->input('password'),
        );

        if (\auth()->attempt($credentials)) {
            DB::beginTransaction();
            $request->request->add(['mac' => User::getMac()]);
            IPData::log($request);
            $user = auth()->user();
            $user->last_login = Carbon::now();
            $user->save();
            $success['phone'] = $user->phone;
            $success['token'] = $user->api_token;

            DB::commit();
            return ApiWrapper::sendResponse($success, "SUCCESS");
        }
        return ApiWrapper::sendResponse(["message" => "auth failed"], "ERROR", 403);
    }
}
