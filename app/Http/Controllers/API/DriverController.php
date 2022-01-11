<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Driver\StoreRequest;
use App\Http\Requests\AssignDriver;
use App\Lib\ApiWrapper;
use App\Models\DriverCarRelation;
use App\Models\DriverData;
use App\Models\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function assign_driver(AssignDriver $request): \Illuminate\Http\JsonResponse
    {
        DriverCarRelation::query()->create([
            'vehicle_id' => $request->input('device_id'),
            'driver_id' => $request->input('driver_id')
        ]);

        return ApiWrapper::sendResponse(['message'=>"SUCCESS"],'SUCCESS',201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated_data = $request->all();

        $file_data = FileManager::storeImage($request, 'avatar');
        if (!is_null($file_data) && isset($file_data->id)) {
            $validated_data['avatar_id'] = $file_data->id;
        }

        $driver = DriverData::query()->create($validated_data);

        return ApiWrapper::sendResponse(['driver_data' => $driver], 'SUCCESS');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $driver = DriverData::query()->find($id);
        if (empty($driver)) {
            return ApiWrapper::sendResponse(['error' => 'not driver by id'], 'ERROR');
        }
        $driver->append('image');
        return ApiWrapper::sendResponse(['driver data' => $driver], 'SUCCESS',201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreRequest $request, $id)
    {
        DB::beginTransaction();
        $driver = DriverData::query()->find($id);
        if (empty($driver)) {
            return ApiWrapper::sendResponse(['error' => 'not driver by id'], 'ERROR');
        }

        $validated_data = $request->all();
        $file_data = FileManager::storeImage($request, 'avatar');
        if (!is_null($file_data) && isset($file_data->id)) {
            $validated_data['avatar_id'] = $file_data->id;
            if (!is_null($driver->avatar_id)) {
                $old_file = FileManager::query()->find($driver->avatar_id);
                if (!empty($old_file)) {
                    $old_file->delete();
                }
            }
        }

        $driver->update($validated_data);

        DB::commit();
        return ApiWrapper::sendResponse(['driver_data' => $driver], 'SUCCESS');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $driver = DriverData::query()->find($id);
        if (empty($driver)) {
            return ApiWrapper::sendResponse(['error' => 'not driver by id'], 'ERROR');
        }
        $driver->delete();
        return ApiWrapper::sendResponse(['message' => "successfully deleted"], 'SUCCESS');
    }
}
