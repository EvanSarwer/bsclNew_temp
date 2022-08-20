<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeselectPeriod extends Model
{
    use HasFactory;
    protected $table = 'deselect_periods';
    public $timestamps = false;
    protected $fillable =['user_id', 'start_date','end_date'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
