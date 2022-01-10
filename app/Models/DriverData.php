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
        'avatar_id'
    ];
    protected $appends = ['image'];

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

}
