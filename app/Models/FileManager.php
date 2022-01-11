<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManager extends Model
{
    use HasFactory, SoftDeletes;

    protected array $fillable = [
        'path', 'name'
    ];

    public static function storeImage($request, $key)
    {
        if ($request->hasFile($key)) {

            $name = $request->file($key)->getClientOriginalName();

            $path = $request->file($key)->store('driver/avatars', ['disk' => 'public']);
            $file_data = FileManager::query()->create([
                'path' => $path,
                'name' => $name
            ]);
            return $file_data;
        }
        return null;
    }
}
