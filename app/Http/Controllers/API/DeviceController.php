<?php

namespace App\Http\Controllers\API;

use App\Lib\ApiWrapper;
use App\Models\GISdata;
use App\Models\DriverData;
use App\Models\VehicleData;
use App\Models\asyncActions;
use App\Http\Requests\GeoQuery;
use App\Models\DriverCarRelation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\QRAssignRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Requests\RegisterDeviceRequest;
use Illuminate\Http\Request;
use App\Http\Requests\API\Device\getStatisticsRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQR;
use App\Models\IntegrationLog;
use Carbon\Carbon;

class DeviceController extends Controller
{
    public function getDriver($id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        $device = VehicleData::query()->find($id);
        if (empty($device)) {
            return ApiWrapper::sendResponse(['message' => 'could not found device with this id'], 'ERROR');
        }
        $relation = DriverCarRelation::query()->where('vehicle_id',$device->id)
            ->pluck('driver_id')->toArray();
        $drivers = DriverData::query()->whereIn('id',$relation)->get();

        DB::commit();
        return ApiWrapper::sendResponse(['data' => $drivers], 'SUCCESS');
    }

    public function update(UpdateDeviceRequest $request){
        $data = $request->validated();
        $car = VehicleData::query()->where('id',$data['car_id'])->update($data);
        return ApiWrapper::sendResponse(['data' => $car], 'SUCCESS');
    }
    public function destroy(Request $request, $device_id){
        $car = VehicleData::query()->where('id',$device_id)->delete();
        $res = ['data' => $car];
        IntegrationLog::log($request, [$res, 'SUCCESS']);
        return ApiWrapper::sendResponse($res, 'SUCCESS');
    }
    
    public function gasStatistics(getStatisticsRequest $reqquest){
        $mode = $reqquest->input('mode');
        $start_time = $reqquest->input('start_time');
        $query = GISdata::query();
        $message = 'SUCCESS';
        try{
            switch($mode){
                case "0":
                    // hour
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(hour from datetime) as hour'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',Carbon::parse($start_time)->endOfDay()->format('Y-m-d H:i:s'))
                            ->groupBy('hour')
                            ->get();
                            break;
                case "1":
                    // month
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(month from datetime) as month'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',Carbon::parse($start_time)->endOfMonth()->format('Y-m-d H:i:s'))
                            ->groupBy('month')
                            ->get();
                            break;
                case "2":
                    // Year
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(month from datetime) as year'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',Carbon::parse($start_time)->endOfYear()->format('Y-m-d H:i:s'))
                            ->groupBy('year')
                            ->get();
                            break;
                default:
                    $data = "mode undefined !";
    
            }
        }catch(\Exception $e){
            $data = $e->getMessage();
            $message = "ERROR";
        }
        return ApiWrapper::sendResponse(['data'=>$data,'mode' => print_r($mode,1), "start_time" => $start_time], $message);
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
        $res = array();
        try {
            switch ($request->input('mode', 0)) {
                case 0:
                    $res = $this->paginate_geo($request);
                    break;
                case 1:
                    $res = $this->turnoff_device($request);
                    break;
                case 2:
                    // get device driver
                    $driver_id = DriverCarRelation::query()
                        ->where('vehicle_id',$request->input('device_id'))->pluck('driver_id')->toArray();
                    $res['data'] = DriverData::query()->whereIn('id',$driver_id)->first();
                    break;
                case 3:
                    // get device data
                    $res['data'] = VehicleData::query()->find($request->input('device_id'));
                    break;
                default:
                    throw new \Exception("set mode please !");

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
