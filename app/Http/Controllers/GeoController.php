<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\GISdata;
use App\Models\IntegrationLog;
use App\Models\IPData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function get_geo(GetGeoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $device_id = $request->post("device_id");
            $data = $this->get_geo_req(
                $request->post("gps_data"),
                $device_id,
                $request->post("relay_state"),
                $request->post("fual_gas")
            );
            $gis = GISdata::create($data);
            if ($request->has('all_data')){
                foreach ($request->post('all_data') as $geo_data){
                    $data = $this->get_geo_req(
                        $geo_data['gps_data'],
                        $device_id,
                        $geo_data['relay_state'],
                        $geo_data['fual_gas']
                    );
                    $gis = GISdata::create($data);
                }
            }
            IPData::log($request);
            $res = ["gis" => $gis];
            $msg = "SUCCESS";
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        IntegrationLog::log($request, [$res,$msg]);
        return ApiWrapper::sendResponse($res,$msg);
    }
//a4LQHmUqO4o7v9IbnziOAI7TTauAzKwymZm9xBEnNt60RgmW57mPgixSfYi1blPg5paapYKFnzPCEOfO
    private function get_geo_req(array $geo_data,int $device_id,bool $relay_state,float $gas, $label = null): array
    {
        return [
            "vehicle_id" => $device_id,
            "relay_state" => $relay_state,
            "gas" => $gas,

            "lat" => $geo_data["lat"],
            "long" => $geo_data["long"],
            "label" => $label,
            "datetime" => Carbon::createFromFormat("d:m:Y H:i:s",$geo_data["datetime"])->format("Y-m-d H:i:s"),
            "speed" => $geo_data["speed"],
        ];

    }
}
