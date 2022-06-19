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

/* Sabbir part */

Route::post('/reach/percent', [SabbirApiController::class, 'reachpercent']);
Route::post('/reachuser', [SabbirApiController::class, 'reachuser']);
Route::post('/tvrgraph', [SabbirApiController::class, 'tvrgraph']);
Route::post('/tvrgraphallchannel', [SabbirApiController::class, 'tvrgraphallchannel']);
Route::post('/tvrgraphallchannelzero', [SabbirApiController::class, 'tvrgraphallchannelzero']);
Route::post('/timecheck', [SabbirController::class, 'timecheck']);
Route::post('/timespent', [SabbirApiController::class, 'timespent']);
Route::post('/share', [SabbirApiController::class, 'share']);
Route::get('/userstat', [SabbirApiController::class, 'userstat']);
Route::get('/channels/viewall', [SabbirApiController::class, 'channels']);
Route::get('/demo', [SabbirApiController::class, 'demo']);
Route::get('/homecount', [SabbirApiController::class, 'homecount']);
/* Sabbir part */


////////////Sarwer Routes///////
Route::post('/tvrshare1p',[SarwerAPIController::class,'tvrshare1p']);
Route::post('/tvrshare',[SarwerAPIController::class,'tvrshare']);
Route::post('/tvrprac',[SarwerAPIController::class,'tvr']);
Route::post('/channeltimespent',[SarwerAPIController::class,'timespent']);
Route::post('/usertimespent',[SarwerAPIController::class,'usertimespent']);
Route::post('/activeuserlist',[SarwerAPIController::class,'activeuserlist']);
Route::get('/activeuserlist',[SarwerAPIController::class,'activeuserlistget']);

Route::post('/activechannellist',[SarwerAPIController::class,'activechannellist']);
Route::get('/activechannellist',[SarwerAPIController::class,'activechannellistget']);
Route::post('/testing',[SarwerAPIController::class,'ttestApi']);

///////////END////////////

//=========Auth Start=========//
Route::post('/auth/sign-in', [AuthController::class, 'signIn']);
Route::post('/auth/sign-up', [AuthController::class, 'signUp']);
Route::get('/auth/current-user', [AuthController::class, 'currentUser']);
//=========Auth End==========//

//////////Dashboard///////////
Route::get('/dashboard/activechannellist',[DashboardController::class,'activechannellistget']);
Route::get('/dashboard/activeuserlist',[DashboardController::class,'activeuserlistget']);
Route::get('/reachuser/dashboard', [DashboardController::class, 'reachuserdashboard']);
Route::get('/reach/percent/dashboard', [DashboardController::class, 'reachpercentdashboard']);
Route::get('/tvrgraph/dashboard', [DashboardController::class, 'tvrgraphdashboard']);
Route::get('/tvrgraphzero/dashboard', [DashboardController::class, 'tvrgraphzerodashboard']);
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
Route::get('/getuserlist',[UserController::class,'getallList']);

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
Route::post('channel/definedtrend', [ChannelController::class, 'definedtrend']);
//////////END/////////////////

Route::get("/test",[UserController::class,'demo_test']);