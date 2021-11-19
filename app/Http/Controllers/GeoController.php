<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\GISdata;
use App\Models\IntegrationLog;
use App\Models\IPData;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function get_geo(GetGeoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();
            $gis = GISdata::create($data);
            IPData::log($request);
            $res = ApiWrapper::sendResponse(["gis" => $gis], "SUCCESS");
        } catch (\Exception $e) {
            $res = ApiWrapper::sendResponse(["message" => $e->getMessage()], "ERROR");
        }
        IntegrationLog::log($request,$res);
        return $res;
    }
}
