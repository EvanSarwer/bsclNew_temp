<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Notification;
use App\Models\Package;
use App\Models\Channel;
use App\Models\Device;
use App\Models\Operator;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'type', 'gender','dob','device_id','user_index'
        // 'email',
        // 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function watchedChannels(){
        return $this->belongsToMany(Channel::class, 'view_logs');
    }

    public function viewLogs(){
        return $this->hasMany(ViewLog::class);
    }

    public function deselectLogs(){
        return $this->hasMany(DeselectLog::class, 'user_id');
    }

    public function operator(){
        return $this->belongsTo(Operator::class);
    }
    public function device(){
        return $this->belongsTo(Device::class,'device_id');
    }

    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
