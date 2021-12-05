<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class asyncActions extends Model
{
    use HasFactory;

    protected $table = 'async_actions';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        "command",
        "command_int",
        "completed",
        "user_id",
        "vehicle_id",
        "comment",
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VehicleData::class,'id','vehicle_id');
    }
}