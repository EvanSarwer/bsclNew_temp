<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppUser;

class Notification extends Model
{
    use HasFactory;
    protected $table = "notifications";
    public $timestamps = false;


    public function user(){
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}
