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
        $device_ids = VehicleData::query()
            ->where('owner_id',auth()->id())
            ->pluck('id')->toArray();
        $drivers_ids = DriverCarRelation::query()
            ->whereIn('vehicle_id',$device_ids)
            ->pluck('driver_id')->toArray();
        $drivers = DriverData::query()->whereIn('id',$drivers_ids)->get();

        return ApiWrapper::sendResponse(['drivers' => $drivers],'SUCCESS');
    }
    public function getVehicle(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        return ApiWrapper::sendResponse(['vehicles' => $user->vehicles],'SUCCESS');
    }
}
