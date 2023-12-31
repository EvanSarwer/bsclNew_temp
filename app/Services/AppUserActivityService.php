<?php

namespace App\Services;

use App\Models\Token;
use App\Models\UserActivityLog;
use App\Models\UserLoginSession;

class AppUserActivityService
{
    public function AppUserReportGenarateOldLogSubmit($tokenString = null, $requestQuery, $result= null )
    {
        if($tokenString == null){
            return false;
        }
        $token = Token::where('token', $tokenString)->first();
        if($token == null){
            return false;
        }

        $loginSession = UserLoginSession::where('user_id', $token->user_id)->where('token', $token->token)->whereNull('end')->first();
        if($loginSession == null){
            return false;
        }

        $dataObj = new \stdClass();
        if($requestQuery){
            $dataObj = clone $requestQuery;

            if ($result !== null) {
                $dataObj->result_row = $result->row ?? 0;
            }
        } else {
            return "something wrong";
        }

        $log = new UserActivityLog();
        $log->session_id = $loginSession->id;
        $log->user_id = $token->user_id;
        $log->operation_type = "report_generate_old";
        $log->date_time = date('Y-m-d H:i:s');
        $log->data = json_encode($dataObj);
        $log->save();

        return true;
    
    }
}

