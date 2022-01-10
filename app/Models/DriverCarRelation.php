<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCarRelation extends Model
{
    use HasFactory;
    protected $fillable = [
        'vehicle_id',
        'driver_id',
    ];
}
