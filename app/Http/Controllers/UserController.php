<?php

namespace App\Http\Controllers;

use App\Lib\ApiWrapper;
use App\Models\DriverCarRelation;
use App\Models\DriverData;
use App\Models\VehicleData;
use Illuminate\Http\Request;
use App\Http\Requests\API\USER\GetVehicles;
use App\Http\Requests\API\USER\SearchVehicle;

class UserController extends Controller
{
    public function getDrivers(): \Illuminate\Http\JsonResponse
    {
        $drivers = DriverData::query()->where('owner_id',auth()->id())->get();

        return ApiWrapper::sendResponse(['drivers' => $drivers],'SUCCESS');
    }
    public function getVehicle(SearchVehicle $request): \Illuminate\Http\JsonResponse
    {
        return ApiWrapper::sendResponse(['vehicles' => $this->paginateVehicle($request)],'SUCCESS');
    }
    private function paginateVehicle(SearchVehicle $request): array
    {
        $query = VehicleData::query();
        $query->where('owner_id', auth()->id());
        if ($car_number = $request->input('car_number')) {
            $query->where('datetime', 'like', $car_number.($request->input('strict') ? "" : "%"));
        }
        $page_size = $request->input('page_size', 10);
        $page = $request->input('page', 0);
        $total = $query->count();
        $data = $query->offset(($page - 1) * $page_size)->limit($page_size)->get();
        return [
            'data' => $data,
            'total_data_count' => $total,
            'total_page_count' => round($total / $page_size),
            'page' => $page,
            'page_size' => $page_size
        ];
    }
}
