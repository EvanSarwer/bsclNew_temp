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
use App\Models\PasswordReset;
use App\Mail\SendMail;
use App\Models\EmailVerification;
use App\Models\UserLoginSession;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //
    public function signIn(Request $req)
    {
        $user = Login::where('user_name', $req->username)->where('password', md5($req->password))->first();
        if ($user) {
            $old_tokens = Token::where('user_id',$user->id)->delete();
            if($user->active == 1){
                // User Login Old Session End
                $userLoginSession = UserLoginSession::where('user_id', $user->id)->whereNull('end')->first();
                if($userLoginSession){
                    $userLoginSession->end = date('Y-m-d H:i:s');
                    $userLoginSession->save();
                }

                // User Login Token Create & Save
                $tokenGen = bin2hex(random_bytes(37));
                $token = new Token();
                $token->value = md5($tokenGen);
                $token->user_id = $user->id;
                $token->token = $tokenGen;
                $token->save();
                $data = array("role"=>$user->role,"username"=>$user->user_name,"token"=>$tokenGen);
                $user->try_time = 0;
                $user->last_request = date('Y-m-d H:i:s');
                $user->save();



                // User Login Session Create
                $userLoginSession = new UserLoginSession();
                $userLoginSession->user_id = $user->id;
                $userLoginSession->start = date('Y-m-d H:i:s');
                $userLoginSession->token = $tokenGen;
                $userLoginSession->save();

                return response()->json(["data" => (object)$data, "error" => null], 201);
            } else {
                return response()->json(["data" => null, "error" => "Account Is Deactive"], 423);
            }
            
        } else {
            $falseTry_user = Login::where('user_name', $req->username)->first();
            if($falseTry_user){
                if($falseTry_user->try_time > 3){
                    $falseTry_user->active = 0;
                    $falseTry_user->save();
                    return response()->json(["data" => null, "error" => "Too Many Wrong Try"], 422);

                } else {

                    $falseTry_user->try_time += 1;
                    $falseTry_user->save();
                    return response()->json(["data" => null, "error" => "USERNAME OR PASSWORD IS INCORRECT"], 422);
                }
                
            }
            return response()->json(["data" => null, "error" => "USERNAME OR PASSWORD IS INCORRECT"], 422);
        }
    }

    public function forgetPassEmail(Request $req){
        $user = Login::where('email', $req->email)->first();
        if ($user){
            $passToken = PasswordReset::where('email', $req->email)->get();
            if($passToken){
                foreach($passToken as $pt){
                    $pt->delete();
                }
            }

            $tokenGen = bin2hex(random_bytes(37));
            $token = new PasswordReset();
            $token->email = $user->email;
            $token->value = md5($tokenGen);
            $token->token = $tokenGen;
            $token->created_at = date('Y-m-d H:i:s');
            $token->save();

            $mail = new SendMail("BSCL Reset Password Verification",$user->user_name, $tokenGen);
            Mail::to($user->email)->send($mail);

            return response()->json(["msg" =>"Check your email to get the reset link", "error" => null], 201);
        }
        else{
            return response()->json(["data" => null, "error" => "Email Not Exist"], 422);
        }
    }

    public function forgetPassTokenValidation(Request $req){
        $token= PasswordReset::where('value',md5($req->token))->first();
        if($token){
            $CurrentTime = date("Y-m-d H:i:s");
            $min = 10;
            $newtimestamp = strtotime("{$token->created_at} + {$min} minute");
            $ValidTime = date('Y-m-d H:i:s', $newtimestamp);

            if( (strtotime($CurrentTime)) <= (strtotime($ValidTime)) ){
                return response()->json(["email" =>$token->email], 200);
            }
            return response()->json(["err" =>"Token Time out"], 422);
        }
        return response()->json(["err" =>"Invalid Token"], 422);
    }

    public function forgetPassSubmit(Request $req){
        $passToken= PasswordReset::where('email',$req->email)->where('value',md5($req->token))->first();
        if($passToken){
            $user= Login::where('email',$passToken->email)->first();
            if($req->confirmpass==$req->newpassword){
                $user->password=md5($req->newpassword);
                $user->save();

                $tokenGen = bin2hex(random_bytes(37));
                $token = new Token();
                $token->value = md5($tokenGen);
                $token->user_id = $user->id;
                $token->token = $tokenGen;
                $token->save();
                $data = array("role"=>$user->role,"username"=>$user->user_name,"token"=>$tokenGen);

                $passToken->delete();

                return response()->json(["data" => (object)$data,"msg" =>"Password changed Successfully","err"=> null], 200);
            }
            return response()->json(["err" =>"Passwords Doesn't match"], 200);
        }
        return response()->json(["err" =>"Invalid Token"], 422);
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
        return response()->json(["data" => $userToken->user_id, "error" => null], 200);
    }
    function logout(Request $req){
        $token = $req->header('Authorization');
        $userToken = Token::where('token', $token)->first();
        if (!$userToken) return response()->json(["data" => null, "error" => "Invalid Token"], 404);
        Login::where('id', $userToken->user_id)->update(['last_request' => date('Y-m-d H:i:s')]);       //record user last response time
        $userToken->delete();

        // User Login Session End
        $userLoginSession = UserLoginSession::where('user_id', $userToken->user_id)->where('token', $token)->whereNull('end')->first();
        if($userLoginSession){
            $userLoginSession->end = date('Y-m-d H:i:s');
            $userLoginSession->save();
        }

        return response()->json(["msg"=>"Logged Out"],200);

    }
    function git_id(){
        
        $path = base_path('.git/');

    if (! file_exists($path)) {
        return null;
    }

    $head = trim(substr(file_get_contents($path . 'HEAD'), 4));

    $hash = trim(file_get_contents(sprintf($path . $head)));

    //return $hash;
                return response()->json(["git_id"=>$hash],200);
    }

    public function deployerCheck(Request $req){
        
        if ($req->code == "k1z2E9-11A22b"){

            $tokenGen = bin2hex(random_bytes(37));
            $token = new EmailVerification();
            $token->value = md5($tokenGen);
            $token->token = $tokenGen;
            $token->created_at = date('Y-m-d H:i:s');
            $token->save();

            return response()->json(["token"=>$tokenGen ,"msg" =>"Matched", "error" => null], 201);
        }
        else{
            return response()->json(["data" => null, "error" => "Not Matched"], 401);
        }
    }

    public function deployerReg(Request $req){
        $token= EmailVerification::where('value',md5($req->token))->first();
        if($token){
            $CurrentTime = date("Y-m-d H:i:s");
            $min = 20;
            $newtimestamp = strtotime("{$token->created_at} + {$min} minute");
            $ValidTime = date('Y-m-d H:i:s', $newtimestamp);

            if( (strtotime($CurrentTime)) <= (strtotime($ValidTime)) ){
                return response()->json(["msg" =>"ok"], 200);
            }
            return response()->json(["err" =>"Time Out"], 401);
        }
        return response()->json(["err" =>"Invalid"], 401);

    }









}
