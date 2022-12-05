<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ViewLog extends Model
{
    use HasFactory;
    protected $table = 'view_logs';
    public $timestamps = false;

    public function channel(){
        return $this->belongsTo(Channel::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
