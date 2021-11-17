<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterRequest;
use App\Lib\ApiWrapper;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function login(RegisterRequest $request){

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;

        return ApiWrapper::sendResponse($success, "SUCCESS");
    }
}
