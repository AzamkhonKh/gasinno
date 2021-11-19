<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\RegisterRequest;
use App\Lib\ApiWrapper;
use App\Models\IntegrationLog;
use App\Models\IPData;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->request->add(['mac' => User::getMac()]);
        IPData::log($request);
        try {
            $input = $request->all();
            if (!isset($input['name'])) {
                $input['name'] = $input['type'] . Str::random();
            }
            $input['role_id'] = Role::where('name', $input['type'])->first()->id;
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);
            $user->last_login = Carbon::now();
            $user->save();
            $success['token'] = $user->createToken('gasInno')->plainTextToken;
            $success['name'] = $user->name;
            $res = $success;
            $msg = "SUCCESS";
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        IntegrationLog::log($request, [$res,$msg]);
        return ApiWrapper::sendResponse($res,$msg);
    }

    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $credentials = array(
            'name' => $request->get('name'),
            'password' => $request->get('password'),
        );

        if (Auth::attempt($credentials)) {
            $request->add(['mac' => User::getMac()]);
            IPData::log($request);
            $user = User::where('name', $request->get('name'))->first();
            $user->last_login = Carbon::now();
            $user->save();
            $success['user'] = $user;
            return ApiWrapper::sendResponse($success, "SUCCESS");
        }
        return ApiWrapper::sendResponse(["message" => "auth failed"], "ERROR", 403);
    }
}
