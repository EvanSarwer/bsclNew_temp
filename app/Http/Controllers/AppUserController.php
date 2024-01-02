<?php

namespace App\Http\Controllers;
use App\Models\AppUser;
use App\Models\DeployerInfo;
use App\Models\Login;
use App\Models\UserLoginSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datetime;

class AppUserController extends Controller
{
    
    function changepass(Request $req){
        
        // $rules = array_diff_key($this->rules(), array_flip((array) ['user_name','password','c_password']));
        // $validator = Validator::make($req->all(),$rules);
        // if ($validator->fails()){
        //     return response()->json($validator->errors(), 422);
        // }
        $user= Login::where('user_name',$req->user_name)->where('password',md5($req->currentpassword))->first();
        if($user){
            if($req->confirmpass==$req->newpassword){
                $user->password=md5($req->newpassword);
                $user->save();
                return response()->json(["msg" =>"Password changed"], 200);
            }
            return response()->json(["err" =>"PASSWORD DOESN'T MATCH"], 422);
        }
        //$user->update(["address"=>$req->address,"email"=>$req->email,"phone"=>$req->phone,"updated_at"=>new Datetime()]);
        //$user = Login::where('user_name',$req->user_name)->first();
        //$user->update(["email"=>$req->email,"updated_at"=>new Datetime(),"updated_by"=>"admin"]);
        return response()->json(["err" =>"CURRENT PASSWORD WRONG"], 422);
    }

    function store(Request $req){    
        $validator = Validator::make($req->all(),$this->rules());
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $user = (object)$req->all();
        $user->password =md5($req->password);
        $user->created_at = new Datetime();
        if($user->role == "deployer"){
            $user->number = $user->phone;
            $user->state_name = $user->address;
            DeployerInfo::create((array)$user);
        }else{
            AppUser::create((array)$user);
        }
        
        Login::create((array)$user);
        return response()->json(["message"=>"User Created Successfully"]);
    }

    function edit(Request $req){
        $rules = array_diff_key($this->rules(), array_flip((array) ['user_name','password','c_password']));
        $rules["email"] = 'required|unique:login,email,'.$req->user_id;
        $validator = Validator::make($req->all(),$rules);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        if($req->role == "deployer"){
            $user= DeployerInfo::where('user_name',$req->user_name)->first();
            $user->update(["state_name"=>$req->address,"email"=>$req->email,"number"=>$req->phone]);
        }else{
            $user= AppUser::where('user_name',$req->user_name)->first();
            $user->update(["address"=>$req->address,"email"=>$req->email,"phone"=>$req->phone,"updated_at"=>new Datetime()]);
        }

        $user = Login::where('user_name',$req->user_name)->first();
        $user->update(["email"=>$req->email,"role"=>$req->role,"updated_at"=>new Datetime(),"updated_by"=>"admin"]);
        return response()->json(["message"=>"User Updated Successfully"]);
    }
    function delete(Request $req){
        // $user= AppUser::where('user_name',$req->user_name)->first();
        // $user->active=0;
        // $user->deleted_by="admin";
        // $user->deleted_at=new Datetime();
        // $user->save();
        $user= Login::where('user_name',$req->user_name)->first();
        $user->active=0;
        $user->deleted_by="admin";
        $user->deleted_at=new Datetime();
        $user->save();
        return response()->json(["message"=>"User Deleted Successfully"]);
    }
    function activateDeactivate(Request $req){
        // $user= AppUser::where('user_name',$req->user_name)->first();
        // $user->active=$req->flag;
        // $user->updated_by="admin";
        // $user->updated_at=new Datetime();
        // $user->save();
        $user= Login::where('user_name',$req->user_name)->first();
        $user->active=$req->flag;
        $user->updated_by="admin";
        $user->updated_at=new Datetime();
        $user->save();
        return response()->json(["message"=>"User Status Updated Successfully"]);
    }
    
    function list(){
        $admins = Login::where('role','admin')->where('deleted_by',null)->get();
        $admin_users = [];
        foreach($admins as $a){
            $admn = AppUser::where('user_name',$a->user_name)->first();
            $admn->active = $a->active;
            $admn->login = $admn->login;
            array_push($admin_users,$admn);
        }

        $channelUsers = Login::where('role','general')->where('deleted_by',null)->get();
        $channel_users = [];
        foreach($channelUsers as $c){
            $chnl = AppUser::where('user_name',$c->user_name)->first();
            $chnl->active = $c->active;
            $chnl->login = $chnl->login;
            array_push($channel_users,$chnl);
        }

        $addAgencies = Login::where('role','add-agency')->where('deleted_by',null)->get();
        $addAgency_users = [];
        foreach($addAgencies as $ad){
            $agency = AppUser::where('user_name',$ad->user_name)->first();
            $agency->active = $ad->active;
            $agency->login = $agency->login;
            array_push($addAgency_users,$agency);
        }

        $deployers = Login::where('role','deployer')->where('deleted_by',null)->get();
        $deployer_users = [];
        foreach($deployers as $d){
            $dep = DeployerInfo::where('user_name',$d->user_name)->first();
            $dep->active = $d->active;
            $dep->login = $dep->login;
            array_push($deployer_users,$dep);
        }

        $operators = Login::where('role','operator')->where('deleted_by',null)->get();
        $operator_users = [];
        foreach($operators as $o){
            $operat = AppUser::where('user_name',$o->user_name)->first();
            $operat->active = $o->active;
            $operat->login = $operat->login;
            array_push($operator_users,$operat);
        }
        
        //$admin_users = $admins->appUser;
        return response()->json(["admin_users"=>$admin_users,"channel_users"=>$channel_users, "addAgency_users"=>$addAgency_users, "deployer_users"=>$deployer_users, "operator_users"=>$operator_users]);
    }



    function get($user_name){
        // $user = AppUser::where('deleted_by',null)->where('user_name',$user_name)->first();
        // $user->role = $user->login->role;
        

        $user = Login::where('user_name',$user_name)->where('deleted_by',null)->first();
        if($user->role == "deployer"){
            $user->address = $user->deployerUser->state_name;
            $user->phone = $user->deployerUser->number;
        }else{
            $user->address = $user->appUser->address;
            $user->phone = $user->appUser->phone;
        }
        
        return response()->json($user);
    }
    function rules(){
        return[
            "user_name"=>"required|alpha_dash|unique:login,user_name|unique:app_users,user_name",
            "email"=>"required|unique:login,email",
            "password"=>"required",
            "c_password"=>"same:password",
            "address"=>"required",
            "phone"=>"required",
            "role"=>"required",

        ];
    }



    function addDeployer(Request $req){    
        $validator = Validator::make($req->all(),$this->deployerRules());
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $user = (object)$req->all();
        $user->password =md5($req->password);
        $user->created_at = new Datetime();
        DeployerInfo::create((array)$user);
        $user->role = "deployer";
        $user->created_by="deployer";
        Login::create((array)$user);
        return response()->json(["message"=>"Information Submitted Successfully"]);
    }

    function deployerRules(){
        return[
            "user_name"=>"required|alpha_dash|unique:login,user_name|unique:deployer_info,user_name|unique:app_users,user_name",
            "education"=>"required",
            "occupation"=>"required",
            "organization_name"=>"required",
            "designation"=>"required",
            "email"=>"required|unique:deployer_info,email|unique:login,email|unique:app_users,email",
            "password"=>"required",
            "c_password"=>"same:password",
            "number"=>"required",
            "doj"=>"required",
            "dob"=>"required",
            "nid"=>"required|unique:deployer_info,nid",
            "employee_id"=>"required|unique:deployer_info,employee_id",
            "house_name"=>"required",
            "house_number"=>"required",
            "road_number"=>"required",
            "state_name"=>"required",
            "district_name"=>"required",
            "division_name"=>"required",
        ];
    }

    function getAppUser(Request $req){
        $data = AppUser::where('user_name',$req->username)->first();
        return response()->json($data);
    }

    function resetPass(Request $req){
        $user= Login::where('user_name',$req->username)->first();
        if($user){
            if($req->newpassword){
                $user->password=md5($req->newpassword);
                $user->save();
                return response()->json(["msg" =>"Password Reset Successful"], 200);
            }
            return response()->json(["err" =>"PASSWORD Reset Faild."], 422);
        }
        return response()->json(["err" =>"User Not Found"], 422);
    }


    public function getUserLoginSessions($user_id){
        $userData = Login::where('id', $user_id)->first();
        $user_login_sessions = UserLoginSession::where('user_id', $user_id)->orderBy('start', 'desc')->get();
        foreach ($user_login_sessions as $session) {
            if($session->end){
                $session->duration = abs(strtotime($session->start) - strtotime($session->end)) / 60;
                $session->duration = number_format($session->duration, 2, '.', '');
            }else{
                $session->duration = abs(strtotime($session->start) - strtotime(date('Y-m-d H:i:s'))) / 60;
                $session->duration = number_format($session->duration, 2, '.', '');
            }
            
        }
        return response()->json(["userData" => $userData, "user_login_sessions" => $user_login_sessions],200);
    }


    public function getUserActivityLog($session_id){
        $user_login_sessions = UserLoginSession::where('id', $session_id)->first();
        if($user_login_sessions){
            $user_activity_logs = $user_login_sessions->userActivityLogs;
            foreach ($user_activity_logs as $log) {
                $log->data_json = $log->data;
                $log->data = json_decode($log->data);
            }
            $userData = $user_login_sessions->login;
            return response()->json(["userData" => $userData, "user_activity_logs" => $user_activity_logs],200);
        }
        return response()->json(["msg" => "error"],402);
    }




}
