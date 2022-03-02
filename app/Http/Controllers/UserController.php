<?php

namespace App\Http\Controllers;

use App\Lib\ApiWrapper;
use App\Models\DriverCarRelation;
use App\Models\DriverData;
use App\Models\VehicleData;
use Illuminate\Http\Request;
use App\Http\Requests\API\USER\GetVehicles;
use App\Http\Requests\API\USER\SearchVehicle;
use App\Http\Requests\API\RegisterRequest;
use Illuminate\Support\Facades\DB;
use App\Models\IPData;
use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

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
    public function updateData(RegisterRequest $request,$id): \Illuminate\Http\JsonResponse
    {
        
        DB::beginTransaction();
        $request->request->add(['mac' => User::getMac()]);
        IPData::log($request);
        try {
            $input = $request->all();
            $input['api_token'] = Str::random(50);
            $input['password'] = Hash::make($input['password']);
            $user = User::updateOrCreate(['id'=>$id],$input);
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
