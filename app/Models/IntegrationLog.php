<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class IntegrationLog extends Model
{
    use HasFactory;
    protected $fillable = ["response","request","api","user_id"];
    /**
     * @var string
     */
    private $response;
    /**
     * @var string
     */
    private $request;
    /**
     * @var mixed
     */
    private $api;
    /**
     * @var int|null
     */
    private $user_id;

    public static function log($request, $response){
        $req = $request->all();
        Log::debug("something new ". Carbon::now());
        Log::debug(print_r($req,1));
        Log::debug(print_r($response,1));
//        $log = new IntegrationLog();
//        $log->user_id = auth()->id() ?? null;
//        $log->api = $request->route();
//        $log->request = print_r($req,1);
//        $log->response = print_r($response,1);
//        $log->save();
        IntegrationLog::create([
            "user_id" => auth()->id() ?? null,
            "api" => print_r($request->path(),1),
            "request" => print_r($req,1),
            "response" => print_r($response,1),
        ]);
    }
}
