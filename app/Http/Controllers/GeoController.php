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
            $res = ["gis" => $gis];
            $msg = "SUCCESS";
        } catch (\Exception $e) {
            $res = ["message" => $e->getMessage()];
            $msg = "ERROR";
        }
        IntegrationLog::log($request, [$res,$msg]);
        return ApiWrapper::sendResponse($res,$msg);
    }
}
