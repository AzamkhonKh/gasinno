<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VehicleData extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "owner_id",
        "balloon_volume",
        "car_number",
        "car_model",
        "token",
        "verified",
        "qr_text",
        "year",
        'deleted_at',
        "texosmotr_valid_till",
        "strxovka_valid_till",
        "tonirovka_valid_till",
        "doverenost_valid_till",
    ];
    
    protected $hidden = [
        'token',
        'created_at',
        'updated_at',
        "verified",
        'qr_text'
    ];

    protected $with = ['current_rs'];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'id','owner_id');
    }
    
    public function geo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GISdata::class,'vehicle_id','id');
    }

    public function current_rs(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(GISdata::class,'vehicle_id','id')->latest();
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->qr_text = Str::uuid()->toString();
        });
    }

}
