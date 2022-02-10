<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GasSuplliedData extends Model
{
    use HasFactory, SoftDeletes;
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        "vehicle_id",
        "lat",
        "long",
        "label",
        "gas",
        "relay_state",
        "restored",
        "speed",
        "datetime"
    ];
}
