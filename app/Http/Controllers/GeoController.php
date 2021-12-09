<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeoQuery;
use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\asyncActions;
use App\Models\GISdata;
use App\Models\IntegrationLog;
use App\Models\IPData;
use App\Models\VehicleData;
use Illuminate\Support\Facades\DB;

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
            $gis = GISdata::create($data);
            IPData::log($request, $device_id);
            $asyn_mode = asyncActions::where("vehicle_id",$device_id)->where('completed',false);
            $actions = $asyn_mode->count();
            if ($actions > 0){
                $res = ["message" => "TURNOFF"];
                $msg = "TURNOFF";
                $asyn_mode->update([
                    'completed' => true
                ]);
            }else{
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

    public function request_geo(GeoQuery $request): \Illuminate\Http\JsonResponse
    {

        try {
            switch ($request->input('mode',0)) {
                case 1:
                    $res = $this->turnoff_device($request);
                    break;
                case 2:
                    $res = $this->assign_device($request);
                    break;
                default:
                    $res = $this->paginate_geo($request);
            }

            $msg = 'SUCCESS';
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
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

    private function paginate_geo(GeoQuery $request): array
    {
        $query = GISdata::query();
        $query->where('vehicle_id', $request->input('device_id'));
        if ($from = $request->input('from')) {
            $query->where('datetime', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('datetime', '<=', $to);
        }
        $page_size = $request->input('page_size', 6);
        $page = $request->input('page', 0);
        $total = $query->count();
        $data = $query->offset(($page - 1) * $page_size)->limit($page_size)->orderByDesc('datetime')->get();
        return [
            'data' => $data,
            'total_data_count' => $total,
            'total_page_count' => round($total/$page_size),
            'page' => $page,
            'page_size' => $page_size
        ];
    }

    private function turnoff_device(GeoQuery $request): array
    {
        $async = asyncActions::create([
            "command" => "turn off device",
            "command_int" => 1,
            "completed" => false,
            "user_id" => auth()->id(),
            "vehicle_id" => $request->input('device_id'),
        ]);
        return [
            'command' => $async,
            'message' => 'will send turn off message'
        ];
    }

    private function assign_device(GeoQuery $request)
    {
        $device = VehicleData::where('id',$request->input('device_id'))->where('owner_id');
    }
}
