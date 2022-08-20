<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeselectLog extends Model
{
    use HasFactory;
    protected $table = 'deselect_logs';
    public $timestamps = false;

    public function channel(){
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
