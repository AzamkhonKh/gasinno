<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected array $fillable = [
        'name',
        'firstname',
        'lastname',
        'api_token',
        'phone',
        'email',
        'password',
        'usb_key',
        'usb_key_validated',
    ];

    /**asa
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'api_token',
        'usb_key_validated',
        'password',
        'remember_token',
        'usb_key',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getMac()
    {
        return exec('getmac') ?? null;
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    public function ip(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IPData::class);
    }

    public function log(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return IntegrationLog::where('user_id', $this->id)->select('api', 'request', 'response')->get();
    }

    public function vehicles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VehicleData::class, 'owner_id', 'id');
    }

    public static function getIp(): ?string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return null;
    }

    public function getRolesAttribute(): array
    {
        return Role::query()
            ->whereIn('id', $this->role()->pluck('role_id')->toArray())
            ->pluck('name')
            ->toArray();
    }

    public function checkRole(string $role): bool
    {
        if (!isset($this->roles) || !is_array($this->roles)) {
            $this->roles = $this->getRolesAttribute();
        }

        foreach ($this->roles as $r) {
            if ($r == $role) {
                return true;
            }
        }
        return false;
    }

}
