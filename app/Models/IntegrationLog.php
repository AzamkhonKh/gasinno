<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

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
        $log = new IntegrationLog();
        $log->user_id = auth()->id();
        $log->api = $request->route();
        $log->request = print_r($request->all(),1);
        $log->response = print_r(json_decode($response));
        $log->save();
    }
}
