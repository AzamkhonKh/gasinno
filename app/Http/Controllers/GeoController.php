<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetGeoRequest;
use App\Lib\ApiWrapper;
use App\Models\GISdata;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function get_geo(GetGeoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();
            $gis = GISdata::create($data);
            return ApiWrapper::sendResponse(["gis" => $gis], "SUCCESS");
        } catch (\Exception $e) {
            return ApiWrapper::sendResponse(["message" => $e->getMessage()], "ERROR");
        }
    }
}
