<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\QRAssignRequest;
use App\Http\Requests\GeoQuery;
use App\Lib\ApiWrapper;
use App\Models\asyncActions;
use App\Models\GISdata;
use App\Models\VehicleData;
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQR;

class DeviceController extends Controller
{
    public function getDriver($id): \Illuminate\Http\JsonResponse
    {
        $device = VehicleData::query()->find($id);
        if (empty($device)) {
            return ApiWrapper::sendResponse(['message' => 'could not found device with this id'], 'ERROR');
        }
        return ApiWrapper::sendResponse(['data' => $device], 'SUCCESS');
    }

    public function getQRCode($id)
    {
        $device = VehicleData::query()->find($id);
        if (empty($device)) {
            return ApiWrapper::sendResponse(['message' => 'could not found device with this id'], 'ERROR');
        }

//        $qr = SimpleQR::size(500)->generate($device->qr_text);
//        print_r($qr);
        return SimpleQR::size(500)->generate($device->qr_text);
    }

    public function assign_device(QRAssignRequest $request): \Illuminate\Http\JsonResponse
    {
        $owner_id = $request->has('user_id') ? $request->input('user_id') : auth()->id();
        $device = VehicleData::query()->where('qr_text', $request->input('qr_token'))->first();
        if (is_null($device->owner_id)) {
            $device->owner_id = $owner_id;
            $device->save();
            $msg = 'SUCCESS';
        } else {
            $msg = 'OWNER ALREADY EXISTS !';
        }
        return ApiWrapper::sendResponse(['device_data' => $device], $msg);
    }

    public function request_geo(GeoQuery $request): \Illuminate\Http\JsonResponse
    {

        try {
            switch ($request->input('mode', 0)) {
                case 1:
                    $res = $this->turnoff_device($request);
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
            'total_page_count' => round($total / $page_size),
            'page' => $page,
            'page_size' => $page_size
        ];
    }

    private function turnoff_device(GeoQuery $request): array
    {
        $prev_action = asyncActions::query()
            ->where('vehicle_id', $request->input('device_id'))
            ->where('completed', 0)
            ->first();
        if (empty($prev_action)) {
            $async = asyncActions::query()->create([
                "command" => "turn off device",
                "command_int" => 1,
                "completed" => false,
                "user_id" => auth()->id(),
                "vehicle_id" => $request->input('device_id'),
            ]);
        } else {
            $async = $prev_action;
        }
        return [
            'command' => $async,
            'message' => 'will send turn off message'
        ];
    }
}
