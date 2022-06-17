<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use App\Models\Token;
use Carbon\Carbon;
use DateTime;
use App\Models\Login;

class AuthController extends Controller
{
    //
    public function signIn(Request $req)
    {
        $user = Login::where('user_name', $req->username)->where('password', md5($req->password))->first();
        if ($user) {
            $tokenGen = bin2hex(random_bytes(37));
            $token = new Token();
            $token->value = md5($tokenGen);
            $token->user_id = $user->id;
            $token->token = $tokenGen;
            $token->save();
            $data = array("role"=>$user->role,"token"=>$tokenGen);
            return response()->json(["data" => (object)$data, "error" => null], 201);
        } else {
            return response()->json(["data" => null, "error" => "Username or password is incorrect"], 401);
        }
    }

    public function signUp(Request $req)
    {
        $existingUser = User::where('user_name', $req->username)->first();
        if ($existingUser) {
            return response()->json(["data" => null, "error" => "Username already exists"], 200);
        }

        $user = new User();
        $user->user_name = $req->username;
        $user->password = md5($req->password);
        $user->address = $req->address;
        $user->user_type = "user";
        $user->lat = "22.341000";
        $user->lng = "91.815530";
        $user->save();

        //$tokenGen = bin2hex(random_bytes(37));

        // $emailToken = new EmailVerifyToken();
        // $emailToken->value = $tokenGen;
        // $emailToken->user_id = $user->id;
        // $emailToken->save();

        // $mail = new SendMail($req->name, $tokenGen);
        // Mail::to($req->email)->send($mail);


        return response()->json(["data" => $user,"error" => null], 201);
    }

    public function currentUser(Request $req)
    {
        $token = $req->header('Authorization');
        $userToken = Token::where('token', $token)->first();
        if (!$userToken) return response()->json(["data" => null, "error" => "Invalid Token"], 404);
        return response()->json(["data" => $userToken->user, "error" => null], 200);
    }
    function logout(Request $req){
        $token = $req->header('Authorization');
        $userToken = Token::where('token', $token)->first()->delete();
        return response()->json(["msg"=>"Logged Out"],200);

    }
}
