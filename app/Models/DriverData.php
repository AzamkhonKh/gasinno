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

}
