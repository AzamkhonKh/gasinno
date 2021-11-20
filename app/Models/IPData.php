<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPData extends Model
{
    use HasFactory;
    protected $table = 'ip_data';
    protected $fillable = [
        "ip",
        "mac",
        "device_id",
        "user_id"
    ];
    public static function log($request,$device_id = null){
        $ip = $request->ip();
        $mac = $request->has('mac') ? $request->mac : null;
        $same = IPData::where('user_id',auth()->id())->where('mac',$mac)->where('ip',$ip)->first();
        if ((!empty($ip) || !empty($mac)) && empty($same)){
            $model = new IPData();
            $model->ip = $ip;
            $model->device_id = $device_id;
            $model->mac = $mac;
            $model->user_id = auth()->id();
            $model->save();
        }
    }
}
