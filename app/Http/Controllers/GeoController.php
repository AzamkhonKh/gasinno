<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\asyncActions;
use App\Models\GISdata;
use App\Models\IntegrationLog;
use App\Models\IPData;
use App\Models\GasSuplliedData;
use App\Http\Requests\GetRelayStateRequest;

class GeoController extends Controller
{
    public function get_geo(GetGeoRequest $request): \Illuminate\Http\JsonResponse
    {
//        DB::beginTransaction();
        try {
            $device_id = $request->input("device_id");
            $data = $this->get_geo_req(
                $request->all(),
                $device_id,
            );
            $gis = GISdata::query()->create($data);
            IPData::log($request, $device_id);
            $asyn_mode = asyncActions::query()->where("vehicle_id", $device_id)->where('completed', false);
            $actions = $asyn_mode->count();
            if ($actions > 0) {
                $action = $asyn_mode->orderBy('id','asc')->first(); // last uncompleted action such as if first off and another on so it will complete first off and after that on
                $msg = $action->command_int == 1 ? "TURNOFF" : "TURNON";
                $res = ["message" => $msg];
                $action->update([
                    'completed' => true
                ]);
            } else {
                $res = ["gis" => $gis];
                $msg = "SUCCESS";
            }
            $relay_state = asyncActions::query()->where("vehicle_id", $device_id)->where('completed', true)->first();
            $relay_state_msg = !empty($relay_state) ? isset($action) && $action->command_int == 1 ? "TURNOFF" : "TURNON" : "TURNON";
        } catch (\Exception $e) {
//            DB::rollBack();
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
            $relay_state_msg = "ERROR";
        }
//        DB::commit();
        IntegrationLog::log($request, [$res, $msg]);

        return ApiWrapper::sendResponse($res, $msg,201,$relay_state_msg);
    }
    public function get_supply_geo(GetGeoRequest $request): \Illuminate\Http\JsonResponse
    {
//        DB::beginTransaction();
        try {
            $device_id = $request->input("device_id");
            $data = $this->get_geo_req(
                $request->all(),
                $device_id,
            );
            $gis = GasSuplliedData::query()->create($data);
            IPData::log($request, $device_id);
            $asyn_mode = asyncActions::query()->where("vehicle_id", $device_id)->where('completed', false);
            $actions = $asyn_mode->count();
            if ($actions > 0) {
                $action = $asyn_mode->first();
                $msg = $action->command_int == 1 ? "TURNOFF" : "TURNON";
                $res = ["message" => $msg];
                $asyn_mode->update([
                    'completed' => true
                ]);
            } else {
                $res = ["gis" => $gis];
                $msg = "SUCCESS";
            }
        } catch (\Exception $e) {
//            DB::rollBack();
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
//        DB::commit();
        IntegrationLog::log($request, [$res, $msg]);

        return ApiWrapper::sendResponse($res, $msg);
    }
    public function get_geo_stopped(GetRelayStateRequest $request): \Illuminate\Http\JsonResponse
    {
//        DB::beginTransaction();
        try {
            $device_id = $request->input("device_id");
            $asyn_mode = asyncActions::query()->where("vehicle_id", $device_id)->where('completed', false);
            $actions = $asyn_mode->count();
            $gis = GISdata::query()->where('vehicle_id',$device_id)->limit(1)->orderBy('id','desc')->update([
                'relay_state' => $request->input('relay_state')
            ]);

            if ($actions > 0) {
                $action = $asyn_mode->orderBy('id','asc')->first(); // last uncompleted action such as if first off and another on so it will complete first off and after that on
                $msg = $action->command_int == 1 ? "TURNOFF" : "TURNON";
                $res = ["message" => $msg];
                $action->update([
                    'completed' => true
                ]);
            } else {
                $res = ["relay_state" => $request->input('relay_state')];
                $msg = "SUCCESS";
            }
            $relay_state = asyncActions::query()->where("vehicle_id", $device_id)->where('completed', true)->first();
            $relay_state_msg = !empty($relay_state) ? isset($action) && $action->command_int == 1 ? "TURNOFF" : "TURNON" : "TURNON";
        } catch (\Exception $e) {
//            DB::rollBack();
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
            $relay_state_msg = "ERROR";
        }
//        DB::commit();
        IntegrationLog::log($request, [$res, $msg]);

        return ApiWrapper::sendResponse($res, $msg,201,$relay_state_msg);
    }


    private function get_geo_req(array $geo_data, int $device_id): array
    {
        return [
            "vehicle_id" => $device_id,
            "relay_state" => $geo_data["relay_state"] ?: false,
            "restored" => $geo_data["restored"] ?? false,
            "gas" => $geo_data["fual_gas"],
            "lat" => $geo_data["lat"],
            "long" => $geo_data["long"],
            "label" => null,
            "datetime" => date("Y-m-d H:i:s", $geo_data["datetime"]),
            "speed" => $geo_data["speed"],
        ];

    }
}
