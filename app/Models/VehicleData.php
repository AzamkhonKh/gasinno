<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VehicleData extends Model
{
    use HasFactory;

    protected array $fillable = [
        "owner_id",
        "balloon_volume",
        "car_number",
        "car_model",
        "token",
        "verified",
    ];

    protected $hidden = [
        'token',
        'created_at',
        'updated_at'
    ];
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'id','owner_id');
    }
    public function geo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GISdata::class,'vehicle_id','id');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->qr_text = Str::uuid()->toString();
        });
    }

}
