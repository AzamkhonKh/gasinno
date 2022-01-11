<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\asyncActions;
use App\Models\GISdata;
use App\Models\IntegrationLog;
use App\Models\IPData;

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
                $res = ["message" => "TURNOFF"];
                $msg = "TURNOFF";
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
