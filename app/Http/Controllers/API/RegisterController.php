<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterRequest;
use App\Lib\ApiWrapper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $input['role_id'] = Role::where('name', $input['type'])->first()->id;
        $input['password'] = Hash::make($input['password']);
        $input['ip_address'] = $request->ip();
        $user = User::create($input);
        $success['token'] = $user->createToken('gasInno')->plainTextToken;
        $success['name'] = $user->name;

        return ApiWrapper::sendResponse($success, "SUCCESS");
    }

    public function login(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        $credentials = array(
            'name' => $request->get('name'),
            'password' => $request->get('password'),
        );
        if (Auth::attempt($credentials)) {
            $user = User::where('name', $request->get('name'))->first();
            $success['user'] = $user;
            return ApiWrapper::sendResponse($success, "SUCCESS");
        }
        return ApiWrapper::sendResponse(["message" => "auth failed"], "ERROR", 403);
    }
}
