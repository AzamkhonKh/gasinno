<?php

namespace App\Http\Controllers;

use App\Lib\ApiWrapper;
use App\Models\DriverCarRelation;
use App\Models\DriverData;
use App\Models\VehicleData;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getDrivers(): \Illuminate\Http\JsonResponse
    {
        $drivers = DriverData::query()->where('owner_id',auth()->id())->get();

        return ApiWrapper::sendResponse(['drivers' => $drivers],'SUCCESS');
    }
    public function getVehicle(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        return ApiWrapper::sendResponse(['vehicles' => $user->vehicles],'SUCCESS');
    }
}
