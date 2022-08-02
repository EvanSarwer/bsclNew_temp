<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name','address','lat','lng', 'type', 'gender', 'age','economic_status', 'socio_status'
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

    public function operator(){
        return $this->belongsTo(Operator::class);
    }
}
