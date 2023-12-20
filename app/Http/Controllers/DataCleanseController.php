<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataCleanse;
use App\Models\DeselectPeriod;
use App\Models\Device;
use App\Models\SystemUniverse;
use App\Models\SystemUniverseAll;
use App\Models\User;
use App\Models\ViewLog;
use App\Models\ViewLogArchive;
use DateTime;

class DataCleanseController extends Controller
{
    //
    function index(){
        $yesterday = date("Y-m-d", strtotime('-1 days'));
        $lastData = DataCleanse::latest('id')->first();
        if(!$lastData || $yesterday > $lastData->date){
            $newData=new DataCleanse();
            $newData->date = $yesterday;
            $newData->status = 0;
            $newData->save();
        }
        $lastUpdatedDate = DataCleanse::where('status',1)->latest('id')->first();
        $updatedData = DataCleanse::all();
        $lastUpdatedData = $updatedData->last(); 
        //return response()->json($lastUpdatedData->date);
        if($yesterday == $lastUpdatedData->date){
            return response()->json(["data"=>[$lastUpdatedData], "lastUpdatedDate"=>$lastUpdatedDate?->date],200);
        }
        
    }

    function cleaningData_Date(Request $req){
        $data = DataCleanse::where('id',$req->id)->first();
        $data->status = 1;
        $data->save();
        //systemUniverseTrigger();
        $this->systemUniverseTrigger();
        return redirect()->route('data.cleanse.alldates');
    }



    function getViewlog(Request $req){
        $data = ViewLog::where('id',$req->id)->first();
        return response()->json($data);
    }
    function cleanData(Request $req){
        $data = ViewLog::where('id',$req->id)->first();
        $arch=new ViewLogArchive();
        $arch->view_log_id = $data->id;
        $arch->channel_id = $data->channel_id;
        $arch->user_id = $data->user_id;
        $arch->started_watching_at = $data->started_watching_at;
        $arch->finished_watching_at = $data->finished_watching_at;
        $arch->duration_minute = $data->duration_minute;
        $arch->save();
        $data->delete();
        return response()->json("Cleaned");

    }

    public function lastCleanedDate(){
        $lastCleanedDate = DataCleanse::where('status',1)->latest('id')->first();
        return response()->json(["lastCleanedDate"=>$lastCleanedDate?->date],200);
    }
    function getDatesBetween($startDate, $endDate) {
        $dateArray = array();
    
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
    
        while ($currentDate < $endDate) {
            
            $currentDate->modify('+1 day');
            $dateArray[] = $currentDate->format('Y-m-d');
        }
    
        return $dateArray;
    }
    function systemUniverseTrigger()
    {
        $endDate = DataCleanse::where('status',1)->latest('id')->first()->date;
        $startDate = systemUniverse::max('date_of_gen');
        $dates = $this->getDatesBetween($startDate, $endDate);
        $this->systemUniverse($dates);
        $this->systemUniverseAll($dates);
    }
    function systemUniverse($dates)
    {
        $deselectedIds = DeselectPeriod::where('end_date', null)->pluck('device_id')->toArray();
        $dids = Device::pluck('id')->toArray();
        $list = [];
        $divisions = ["dhaka", "barishal", "chattogram", "khulna", "mymensingh", "rajshahi", "rangpur", "sylhet"];
        $genders = ["m", "f"];
        $ageGroups = ["0-14", "15-24", "25-34", "35-44", "45 & Above"];
        $secs = ["a", "b", "c", "d", "e"];
        $ageGroupListNumRange = [
            [0, 14],
            [15, 24],
            [25, 34],
            [35, 44],
            [45, 150],
        ];
        
        $cc = 0;
        foreach ($divisions as $division) {
            $deviceIds = Device::where('district', strtolower($division))
                ->whereNotIn('Id', $deselectedIds)
                ->pluck('Id')
                ->toArray();

            
            foreach ($genders as $gender) {
                foreach ($ageGroups as $i => $ageGroup) {
                    foreach ($secs as $s) {
                        $noOfUsers = User::whereIn('Device_Id', $deviceIds)
                            ->where('economic_status', 'like', '%' .  $s. '%')
                            ->where('gender', 'like', '%' .  $gender. '%')
                            ->where('address', 'like', '%' . $division . '%')
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) >= ?', [$ageGroupListNumRange[$i][0]])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) <= ?', [$ageGroupListNumRange[$i][1]])
                            ->whereNotNull('dob')
                            ->whereNotNull('economic_status')
                            ->whereNotNull('gender')
                            ->whereNotNull('address')
                            ->count();
                        $cc = $cc + $noOfUsers;
                        //$list[]
                        foreach ($dates as $date ){
                        $obj = new SystemUniverse([
                            'date_of_gen' => $date,
                            'Gender' => $gender,
                            'Region' => $division,
                            'Sec' => $s,
                            'Age_Group' => $ageGroup,
                            'Universe' => $noOfUsers,
                        ]);
                        $obj->save();}
                    }
                }
            }
        }

        try {
            //SystemUniverse::insert($list);
            return response()->json(['message' => 'done', 'count' => $cc]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
        //return $hash;
        //return response()->json(["systemUniverse" => $systemUniverse,"mindate"=>$minDate,"maxdate"=>$maxDate,"a"=>$age_group,"u"=>$universe], 200);
    }
    function systemUniverseAll($dates)
    {
        //$deselectedIds = DeselectPeriod::where('end_date', null)->pluck('device_id')->toArray();
        $dids = Device::pluck('id')->toArray();
        $list = [];
        $divisions = ["dhaka", "barishal", "chattogram", "khulna", "mymensingh", "rajshahi", "rangpur", "sylhet"];
        $genders = ["m", "f"];
        $ageGroups = ["0-14", "15-24", "25-34", "35-44", "45 & Above"];
        $secs = ["a", "b", "c", "d", "e"];
        $ageGroupListNumRange = [
            [0, 14],
            [15, 24],
            [25, 34],
            [35, 44],
            [45, 150],
        ];
        
        $cc = 0;
        foreach ($divisions as $division) {
            $deviceIds = Device::where('district', strtolower($division))
                //->whereNotIn('Id', $deselectedIds)
                ->pluck('Id')
                ->toArray();

            
            foreach ($genders as $gender) {
                foreach ($ageGroups as $i => $ageGroup) {
                    foreach ($secs as $s) {
                        $noOfUsers = User::whereIn('Device_Id', $deviceIds)
                            ->where('economic_status', 'like', '%' .  $s. '%')
                            ->where('gender', 'like', '%' .  $gender. '%')
                            ->where('address', 'like', '%' . $division . '%')
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) >= ?', [$ageGroupListNumRange[$i][0]])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) <= ?', [$ageGroupListNumRange[$i][1]])
                            ->whereNotNull('dob')
                            ->whereNotNull('economic_status')
                            ->whereNotNull('gender')
                            ->whereNotNull('address')
                            ->count();
                        $cc = $cc + $noOfUsers;
                        //$list[]
                        foreach ($dates as $date ){
                        $obj = new SystemUniverseAll([
                            'date_of_gen' => $date,
                            'Gender' => $gender,
                            'Region' => $division,
                            'Sec' => $s,
                            'Age_Group' => $ageGroup,
                            'Universe' => $noOfUsers,
                        ]);
                        $obj->save();}
                    }
                }
            }
        }

        try {
            //SystemUniverse::insert($list);
            return response()->json(['message' => 'done', 'count' => $cc]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
        //return $hash;
        //return response()->json(["systemUniverse" => $systemUniverse,"mindate"=>$minDate,"maxdate"=>$maxDate,"a"=>$age_group,"u"=>$universe], 200);
    }
}
