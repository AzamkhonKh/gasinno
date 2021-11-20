<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleData extends Model
{
    use HasFactory;

    protected $fillable = [
        "owner_id",
        "balloon_volume",
        "car_number",
        "car_model",
        "token",
        "active",
        "turnOff",
    ];

    public function owner(){
        return $this->belongsTo(User::class,'id','owner_id');
    }



}
