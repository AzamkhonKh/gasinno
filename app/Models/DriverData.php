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
        'phone',
        'licenseData',
        'owner_id'
    ];
    protected $hidden = [
        'avatar_id',
        'deleted_at',
        'licenseData'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['image','licenseData','vehicle_data'];

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
    public function getLicenseDataAttribute(): object
    {
        return (object)json_decode($this->licenseData);
    }
    public function getVehicleDataAttribute()
    {
        $vehicle_ids = DriverCarRelation::query()->where('driver_id',$this->id)->pluck('vehicle_id')->toArray();
        return VehicleData::query()->whereIn('id',$vehicle_ids)->get();
    }

}
