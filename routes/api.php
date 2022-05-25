<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SabbirApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\SarwerAPIController;

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
Route::post('channel/reach/percent', [ChannelController::class, 'reachpercent']);
Route::get('/reach/percent/dashboard', [DashboardController::class, 'reachpercentdashboard']);
Route::post('/reachuser', [SabbirApiController::class, 'reachuser']);
Route::get('/reachuser/dashboard', [DashboardController::class, 'reachuserdashboard']);
Route::post('/tvrgraph', [SabbirApiController::class, 'tvrgraph']);
Route::post('/tvrgraphallchannel', [SabbirApiController::class, 'tvrgraphallchannel']);
Route::get('/tvrgraph/dashboard', [DashboardController::class, 'tvrgraphdashboard']);
Route::post('/tvrgraphallchannelzero', [SabbirApiController::class, 'tvrgraphallchannelzero']);
Route::get('/tvrgraphzero/dashboard', [DashboardController::class, 'tvrgraphzerodashboard']);
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

/////////User Status/////////
Route::post('/user/usertimespent',[UserController::class,'usertimespent']);
Route::post('/user/useralltimeview',[UserController::class,'userAllTimeView']);
Route::get('/getuserlist',[UserController::class,'getallList']);

///////////END//////////////