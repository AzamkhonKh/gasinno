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
use App\Http\Requests\API\Device\getDriver;
use App\Http\Requests\API\Device\getDevice;
use App\Http\Requests\API\Device\sendTurnOff;
use App\Models\GasSuplliedData;
use App\Http\Requests\API\Device\sendUserDevicessTurnAction;
use Illuminate\Foundation\Http\FormRequest;
use TheSeer\Tokenizer\Exception;

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
        $car = VehicleData::query()
        ->where('id',$data['id'])
        ->update($data);
        return ApiWrapper::sendResponse(['data' => $car], 'SUCCESS');
    }
    public function destroy(Request $request, $device_id){
        $car = VehicleData::query()->where('id',$device_id)->delete();
        $res = ['data' => $car];
        IntegrationLog::log($request, [$res, 'SUCCESS']);
        return ApiWrapper::sendResponse($res, 'SUCCESS');
    }
    
    public function getDeviceDriver(getDriver $request){
        $res = array();
        try {
            // get device driver
            $driver_id = DriverCarRelation::query()
                ->where('vehicle_id',$request->input('device_id'))->pluck('driver_id')->toArray();
            $res['data'] = DriverData::query()->whereIn('id',$driver_id)->first();
            $msg = 'SUCCESS';
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        return ApiWrapper::sendResponse($res, $msg);
    }
    
    public function deviceData(getDevice $request){
        $res = array();
        try {
            $res['data'] = VehicleData::query()->find($request->input('device_id'));
            $msg = 'SUCCESS';
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        return ApiWrapper::sendResponse($res, $msg);
    }
    public function turnOffDevice(sendTurnOff $request){
        $res = array();
        try {
            $res = $this->turnoff_device($request);
            $msg = 'SUCCESS';
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        return ApiWrapper::sendResponse($res, $msg);
    }
    public function turnOffUserDevices(sendUserDevicessTurnAction $request){
        $res = array();
        try {
            DB::beginTransaction();
            $owner_id = $request->has('owner_id') ? $request->input('owner_id') : auth()->id();
            $device_ids = VehicleData::query()->where('owner_id',$owner_id)->pluck('id')->toArray();
            foreach($device_ids as $id){
                $res[] = $this->turnoff_device($request,$id);
            }
            $msg = 'SUCCESS';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        return ApiWrapper::sendResponse($res, $msg);
    }
    
    public function gasStatistics(getStatisticsRequest $reqquest){
        $mode = $reqquest->input('mode');
        $start_time = $reqquest->input('start_time');
        $query = GISdata::query();
        $message = 'SUCCESS';
        try{
            switch($mode){
                case "0":
                    $end_time = Carbon::parse($start_time)->endOfMinute()->format('Y-m-d H:i:s');
                    // minute by seconds
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(second from datetime) as second, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('second')
                            ->orderBy('second','asc')
                            ->get();
                            break;
                case "1":
                    $end_time = Carbon::parse($start_time)->endOfHour()->format('Y-m-d H:i:s');
                    // hour by minute
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(minute from datetime) as minute, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('minute')
                            ->orderBy('minute','asc')
                            ->get();
                            break;
                case "2":
                    $end_time = Carbon::parse($start_time)->endOfDay()->format('Y-m-d H:i:s');
                    // day by hour
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(hour from datetime) as hour, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('hour')
                            ->orderBy('hour','asc')
                            ->get();
                            break;
                case "3":
                    $end_time = Carbon::parse($start_time)->endOfMonth()->format('Y-m-d H:i:s');
                    // month by day
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(day from datetime) as day, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('day')
                            ->orderBy('day','asc')
                            ->get();
                            break;
                case "4":
                    $end_time = Carbon::parse($start_time)->endOfYear()->format('Y-m-d H:i:s');
                    // year by monthes
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(month from datetime) as month, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('month')
                            ->orderBy('month','asc')
                            ->get();
                            break;
                case "5":
                    $end_time = Carbon::parse($start_time)->endOfDecade()->format('Y-m-d H:i:s');
                    // decade by Year
                    $data = $query
                            ->select(DB::raw('avg(gas), extract(year from datetime) as year, count(*)'))
                            ->where('datetime','>=',$start_time)
                            ->where('datetime','<=',$end_time)
                            ->groupBy('year')
                            ->orderBy('year','asc')
                            ->get();
                            break;
                default:
                    $data = "mode undefined !";
    
            }
        }catch(\Exception $e){
            $data = $e->getMessage();
            $message = "ERROR";
        }
        return ApiWrapper::sendResponse(['data'=>$data,'mode' => print_r($mode,1), "start_time" => $start_time,'end_time' => $end_time], $message);
    }

    public function getQRCode($id)
    {
        $device = VehicleData::query()->find($id);
        if (empty($device)) {
            return ApiWrapper::sendResponse(['message' => 'could not found device with this id'], 'ERROR');
        }
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
        try 
        {
            $res = $this->paginate_geo($request);
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
    public function paginate_supply(GeoQuery $request): array
    {
        $query = GasSuplliedData::query();
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

    private function turnoff_device(FormRequest $request,$id = null): array
    {
        $vehicle_id = !is_null($id) ? $id : $request->input('device_id');
        if(is_null($vehicle_id)) {
            throw new Exception("vehicle id could not be null");
        }
        $command_int = $request->input('action') == "off" ? 1 : 2;
        $prev_action = asyncActions::query()
            ->where('vehicle_id', $vehicle_id)
            ->where('command_int', $command_int)
            ->where('completed', 0)
            ->first();
        $msg = $request->input('action') == "off" ? "turn off device" : "turn on device";
        if (empty($prev_action)) {
            $async = asyncActions::query()->create([
                "command" => $msg,
                "command_int" => $command_int,
                "completed" => false,
                "user_id" => auth()->id(),
                "vehicle_id" => $vehicle_id,
            ]);
        } else {
            $async = $prev_action;
        }
        return [
            'command' => $async,
            'message' => 'will send ' . $async->command
        ];
    }
}
