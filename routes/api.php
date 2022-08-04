<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SabbirApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\LiveChannelController;
use App\Http\Controllers\SarwerAPIController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\TrendController;
use App\Http\Controllers\DeviceController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});





//=========Auth Start=========//
Route::post('/auth/sign-in', [AuthController::class, 'signIn']);
Route::post('/auth/forgetPassword-Email', [AuthController::class, 'forgetPassEmail']);
Route::post('/auth/forgetPass-Validation', [AuthController::class, 'forgetPassTokenValidation']);
Route::post('/auth/newPassSubmit', [AuthController::class, 'forgetPassSubmit']);
Route::post('/auth/sign-up', [AuthController::class, 'signUp']);
Route::get('/auth/current-user', [AuthController::class, 'currentUser'])->middleware('auth.admin');
//=========Auth End==========//



////////////////Excel//////////////

Route::post('/excel/reachp', [ExcelController::class, 'reachp']);
Route::post('/excel/reach0', [ExcelController::class, 'reach0']);
Route::post('/excel/tvr0', [ExcelController::class, 'tvr0']);
Route::post('/excel/tvrp', [ExcelController::class, 'tvrp']);
////////////////Excel//////////////

////////////////Device//////////////

Route::get('/device/tvoff', [DeviceController::class, 'tvoff']);
Route::get('/device/deviceoff', [DeviceController::class, 'deviceOff']);
Route::get('/device/currentlywatching', [DeviceController::class, 'currentlyWatching']);
////////////////Device//////////////


//////////Dashboard///////////
Route::get('/dashboard/CurrentStatusUser', [DashboardController::class, 'CurrentStatusUser']);
Route::get('/dashboard/CurrentStatusTopReach', [DashboardController::class, 'CurrentStatusTopReach']);
Route::get('/dashboard/CurrentStatusTopTvr', [DashboardController::class, 'CurrentStatusTopTvr']);
Route::get('/dashboard/activechannellist',[DashboardController::class,'activechannellistget']);
Route::get('/dashboard/activeuserlist',[DashboardController::class,'activeuserlistget']);
Route::get('/reachuser/dashboard', [DashboardController::class, 'reachuserdashboard']);
Route::get('/reach/percent/dashboard', [DashboardController::class, 'reachpercentdashboard']);
Route::get('/tvrgraph/dashboard', [DashboardController::class, 'tvrgraphdashboard']);
Route::get('/tvrgraphzero/dashboard', [DashboardController::class, 'tvrgraphzerodashboard']);
Route::get('/dashboard/timespentuni', [DashboardController::class, 'timeSpentUniverse']);

Route::get('/sharegraph/dashboard', [DashboardController::class, 'sharegraphdashboard']);
/////////END////////////

//////////Overview///////////
Route::post('/overview/reachusergraph',[OverviewController::class,'reachusergraph']);
Route::post('/overview/reachpercentgraph',[OverviewController::class,'reachpercentgraph']);
Route::post('/overview/tvrgraphallchannelzero',[OverviewController::class,'tvrgraphallchannelzero']);
Route::post('/overview/tvrgraphallchannelpercent',[OverviewController::class,'tvrgraphallchannelpercent']);
Route::post('/overview/tvrsharegraph',[OverviewController::class,'tvrsharegraph']);
Route::post('/overview/timespentgraph',[OverviewController::class,'timespentgraph']);
//////////END///////////////

/////////User Status/////////
Route::post('/user/logs',[UserController::class,'logs']);
Route::post('/user/usertimespent',[UserController::class,'usertimespent']);
Route::post('/user/useralltimeview',[UserController::class,'userAllTimeView']);
Route::post('/user/userdaytimeviewlist',[UserController::class,'userDayTimeViewList']);
Route::post('/user/LastSeventyTwoViewsGraph',[UserController::class,'LastSeventyTwoViewsGraph']);
Route::post('/user/LastTweentyFourViewsGraph',[UserController::class,'LastTweentyFourViewsGraph']);
Route::post('/user/last24WatchingData',[UserController::class,'last24WatchingData']);
Route::post('/user/last72WatchingData',[UserController::class,'last72WatchingData']);
Route::get('/getuserlist',[UserController::class,'getallList']);
Route::post('/user/deviceinfo',[UserController::class,'device_info']);

//
Route::post('/user/userdefined/usertimespent',[UserController::class,'usertimespent2']);

///////////END//////////////

//////////Live Channels/////////
Route::post('/livechannel/activechannellistgraph',[LiveChannelController::class,'activechannellistgraph']);

///////////END////////////////

//////////Channels////////////
Route::get('trend/channels', [ChannelController::class, 'trendchannel']);
Route::post('trend/reach/zero', [ChannelController::class, 'reachtrend']);
Route::post('trend/reach/percent', [ChannelController::class, 'reachpercenttrend']);
Route::post('trend/tvr/percent', [ChannelController::class, 'tvrtrend']);
Route::post('trend/tvr/zero', [ChannelController::class, 'tvrtrendzero']);
Route::post('channel/reach/percent', [ChannelController::class, 'reachpercent']);
Route::post('channel/definedtrendreachp', [ChannelController::class, 'definedtrendreachp']);
Route::post('channel/definedtrendreachp', [ChannelController::class, 'definedtrendreachp']);
Route::post('channel/definedtrendreach0', [ChannelController::class, 'definedtrendreach0']);
Route::post('channel/rangedtrendreach0', [TrendController::class, 'rangedtrendreach0']);
Route::post('channel/rangedtrendreach0', [TrendController::class, 'rangedtrendreachp']);
Route::post('channel/definedtrendtvrp', [ChannelController::class, 'definedtrendtvrp']);
Route::post('channel/definedtrendtvr0', [ChannelController::class, 'definedtrendtvr0']);
//////////END/////////////////

Route::post('/appuser/changepass', [AppUserController::class, 'changepass']);
//Tanvir APIs//
Route::post('/appuser/create',[AppUserController::class,'store']);
Route::post('/appuser/edit',[AppUserController::class,'edit']);
Route::post('/appuser/delete',[AppUserController::class,'delete']);
Route::any('/appuser/activate',[AppUserController::class,'activateDeactivate']);
Route::get('/appuser/list',[AppUserController::class,'list']);
Route::get('/appuser/get/{user_name}',[AppUserController::class,'get']);
Route::get('/logout',[AuthController::class,'logout']);

Route::get('/receive',[RequestController::class,'receive']);
//////////END/////////////////

Route::get("/test",[UserController::class,'demo_test']);