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
        'avatar_id',
        'owner_id'
    ];
    protected $hidden = [
        'deleted_at',
        'licenseData'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $appends = ['image','license'];

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
    public function getLicenseAttribute(): object
    {
        return (object)json_decode($this->licenseData);
    }

}
