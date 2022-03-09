<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverData extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'age',
        'licenseData',
        'phone',
        'owner_id'
    ];
    protected $hidden = [
        'avatar_id',
        'licenseData',
        'deleted_at'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['image','vehicle_data','licenseType'];

    public function driverCarRelation()
    {
        return $this->belongsTo(DriverCarRelation::class, 'id', 'driver_id');
    }
    public function avatar()
    {
        return $this->belongsTo(FileManager::class, 'avatar_id', 'id');
    }

    public function getImageAttribute()
    {
        $file = FileManager::query()->find($this->avatar_id);
        if (empty($file)){
            $path = "driver/avatars/default.png";
        }else{
            $path = $file->path;
        }
        return asset('/uploads/' . $path);
    }
    public function getLicenseTypeAttribute()
    {
        if(isset($this->licenseData)){
            $decoded = json_decode($this->licenseData);
        }else{
            $decoded = ["not exists"];
        }
        return 
            isset($decoded->type) 
            ? $decoded->type 
            : $decoded;
        // return print_r($decoded,1);
    }
    public function getVehicleDataAttribute()
    {
        $vehicle_ids = DriverCarRelation::query()->where('driver_id',$this->id)->pluck('vehicle_id')->toArray();
        return VehicleData::query()->whereIn('id',$vehicle_ids)->get();
    }

}
