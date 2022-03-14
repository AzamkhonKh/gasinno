<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Http\Request;
use App\Models\VehicleData;

class isOwnerOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->checkRole('administrator')){
            return $next($request);
        }elseif(
            VehicleData::query()
                ->where('id',$request->input('device_id'))
                ->where('owner_id',auth()->id())
                ->exists()
            ){
            return $next($request);
            
        }
        abort(403);
    }
}
