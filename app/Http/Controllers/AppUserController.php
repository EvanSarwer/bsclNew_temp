<?php

namespace App\Http\Controllers;
use App\Models\AppUser;
use App\Models\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datetime;

class AppUserController extends Controller
{
    public function __construct()
{
      $this->middleware('auth.admin');
}

    //
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
        $user->created_by="admin";
        $user->created_at = new Datetime();
        AppUser::create((array)$user);
        Login::create((array)$user);
        return response()->json(["message"=>"User Created Successfully"]);
    }
    function edit(Request $req){
        
        $rules = array_diff_key($this->rules(), array_flip((array) ['user_name','password','c_password']));
        $validator = Validator::make($req->all(),$rules);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $user= AppUser::where('user_name',$req->user_name)->first();
        $user->update(["address"=>$req->address,"email"=>$req->email,"phone"=>$req->phone,"updated_at"=>new Datetime()]);
        $user = Login::where('user_name',$req->user_name)->first();
        $user->update(["email"=>$req->email,"updated_at"=>new Datetime(),"updated_by"=>"admin"]);
        return response()->json(["message"=>"User Updated Successfully"]);
    }
    function delete(Request $req){
        $user= AppUser::where('user_name',$req->user_name)->first();
        $user->active=0;
        $user->deleted_by="admin";
        $user->deleted_at=new Datetime();
        $user->save();
        $user= Login::where('user_name',$req->user_name)->first();
        $user->active=0;
        $user->deleted_by="admin";
        $user->deleted_at=new Datetime();
        $user->save();
        return response()->json(["message"=>"User Deleted Successfully"]);
    }
    function activateDeactivate(Request $req){
        $user= AppUser::where('user_name',$req->user_name)->first();
        $user->active=$req->flag;
        $user->updated_by="admin";
        $user->updated_at=new Datetime();
        $user->save();
        $user= Login::where('user_name',$req->user_name)->first();
        $user->active=$req->flag;
        $user->updated_by="admin";
        $user->updated_at=new Datetime();
        $user->save();
        return response()->json(["message"=>"User Status Updated Successfully"]);
    }
    function list(){
        $users = AppUser::where('deleted_by',null)->get();
        return response()->json($users);
    }
    function get($user_name){
        $user = AppUser::where('deleted_by',null)->where('user_name',$user_name)->first();
        return response()->json($user);
    }
    function rules(){
        return[
            "user_name"=>"required|unique:login,user_name",
            "email"=>"required",
            "password"=>"required",
            "c_password"=>"same:password",
            "address"=>"required",
            "phone"=>"required"
        ];
    }
    function messages(){
        return [

        ];
    }
}
