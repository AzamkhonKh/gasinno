<?php

namespace App\Http\Middleware\API;

use App\Models\VehicleData;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GeoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $abort = false;
        $device_id = $request->has('device_id') ? $request->input('device_id') : null;
        $model = VehicleData::find(intval($device_id));
        if (empty($model) || !isset($model->token)) $abort = true;
        $token = $request->has('device_token') ? $request->input('device_token') : null;
//        dd(['device' => $device_id, 'token' => $token, 'abort' => $abort,'model' => $model]);
        if ($abort || empty($token)) abort(400);

        if (Hash::check($token,$model->token))
            return $next($request);
        else
            abort(403);
    }
}
