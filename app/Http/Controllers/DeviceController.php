<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function tvoff()
    {
        $user = User::where('tvoff', 0)->select("id","user_name")->get();
        if ($user) {
            return response()->json(["data" => $user], 200);
        }
    }
}
