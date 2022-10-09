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
Route::get('/auth/current-user', [AuthController::class, 'currentUser']);
Route::post('/auth/deployerCheck', [AuthController::class, 'deployerCheck']);
Route::post('/auth/deployerreg', [AuthController::class, 'deployerReg']);
//=========Auth End==========//



////////////////Excel//////////////

Route::post('/excel/reachp', [ExcelController::class, 'reachp'])->middleware('auth.admin.user');
Route::post('/excel/reach0', [ExcelController::class, 'reach0'])->middleware('auth.admin.user');
Route::post('/excel/tvr0', [ExcelController::class, 'tvr0'])->middleware('auth.admin.user');
Route::post('/excel/tvrp', [ExcelController::class, 'tvrp'])->middleware('auth.admin.user');

Route::post('/adtrp/reachp', [ExcelController::class, 'adtrpreachp'])->middleware('auth.admin.user');
Route::post('/adtrp/reach0', [ExcelController::class, 'adtrpreach0'])->middleware('auth.admin.user');
Route::post('/adtrp/tvr0', [ExcelController::class, 'adtrptvr0'])->middleware('auth.admin.user');
Route::post('/adtrp/tvrp', [ExcelController::class, 'adtrptvrp'])->middleware('auth.admin.user');

Route::post('/adtrpv3/reachp', [ExcelController::class, 'adtrpv3reachp']);//->middleware('auth.admin.user');
Route::post('/adtrpv3/reach0', [ExcelController::class, 'adtrpv3reach0']);//->middleware('auth.admin.user');
// Route::post('/adtrp/reach0', [ExcelController::class, 'adtrpreach0'])->middleware('auth.admin.user');
// Route::post('/adtrp/tvr0', [ExcelController::class, 'adtrptvr0'])->middleware('auth.admin.user');
// Route::post('/adtrp/tvrp', [ExcelController::class, 'adtrptvrp'])->middleware('auth.admin.user');
////////////////Excel//////////////

////////////////Device User//////////////
Route::get('/device/list',[DeviceController::class,'deviceList'])->middleware('auth.admin.deployer');
Route::post('/device/create',[DeviceController::class,'addDevice'])->middleware('auth.admin.deployer');
Route::post('/device/delete',[DeviceController::class,'deleteDevice'])->middleware('auth.admin');
Route::get('/device/get/{device_id}',[DeviceController::class,'getDevice'])->middleware('auth.admin.deployer');
Route::post('/device/edit',[DeviceController::class,'editDevice'])->middleware('auth.admin');
Route::post('/device/deselect',[DeviceController::class,'deselectDevice'])->middleware('auth.admin');


Route::get('/device/tvoff', [DeviceController::class, 'tvoff'])->middleware('auth.admin');
Route::get('/device/deviceoff', [DeviceController::class, 'deviceOff'])->middleware('auth.admin');
Route::get('/device/currentlywatching', [DeviceController::class, 'currentlyWatching'])->middleware('auth.admin');

// Device User
Route::post('/deviceuser/create',[DeviceController::class,'addDeviceUser'])->middleware('auth.admin.deployer');
Route::post('/deviceuser/delete',[DeviceController::class,'deleteDeviceUser'])->middleware('auth.admin');
Route::get('/deviceuser/get/{user_id}',[DeviceController::class,'getDeviceUser'])->middleware('auth.admin.deployer');
Route::post('/deviceuser/edit',[DeviceController::class,'editDeviceUser'])->middleware('auth.admin.deployer');
////////////////Device//////////////


//////////Dashboard///////////
Route::get('/dashboard/CurrentStatusUser', [DashboardController::class, 'CurrentStatusUser'])->middleware('auth.admin.user');
Route::get('/dashboard/CurrentStatusTopReach', [DashboardController::class, 'CurrentStatusTopReach'])->middleware('auth.admin.user');
Route::get('/dashboard/CurrentStatusTopTvr', [DashboardController::class, 'CurrentStatusTopTvr'])->middleware('auth.admin.user');
Route::get('/dashboard/activechannellist',[DashboardController::class,'activechannellistget'])->middleware('auth.admin.user');
Route::get('/dashboard/activeuserlist',[DashboardController::class,'activeuserlistget'])->middleware('auth.admin.user');
Route::get('/reachuser/dashboard', [DashboardController::class, 'reachuserdashboard'])->middleware('auth.admin.user');
Route::get('/reach/percent/dashboard', [DashboardController::class, 'reachpercentdashboard'])->middleware('auth.admin.user');
Route::get('/tvrgraph/dashboard', [DashboardController::class, 'tvrgraphdashboard'])->middleware('auth.admin.user');
Route::get('/tvrgraphzero/dashboard', [DashboardController::class, 'tvrgraphzerodashboard'])->middleware('auth.admin.user');
Route::get('/dashboard/timespentuni', [DashboardController::class, 'timeSpentUniverse'])->middleware('auth.admin.user');
Route::get('/dashboard/notification', [DashboardController::class, 'notification']);

Route::get('/sharegraph/dashboard', [DashboardController::class, 'sharegraphdashboard'])->middleware('auth.admin.user');
/////////END////////////

//////////Overview///////////
Route::post('/overview/reachusergraph',[OverviewController::class,'reachusergraph'])->middleware('auth.admin.user');
Route::post('/overview/reachpercentgraph',[OverviewController::class,'reachpercentgraph'])->middleware('auth.admin.user');
Route::post('/overview/tvrgraphallchannelzero',[OverviewController::class,'tvrgraphallchannelzero'])->middleware('auth.admin.user');
Route::post('/overview/tvrgraphallchannelpercent',[OverviewController::class,'tvrgraphallchannelpercent'])->middleware('auth.admin.user');
Route::post('/overview/tvrsharegraph',[OverviewController::class,'tvrsharegraph'])->middleware('auth.admin.user');
Route::post('/overview/timespentgraph',[OverviewController::class,'timespentgraph'])->middleware('auth.admin.user');
//////////END///////////////

/////////User Status/////////
Route::post('/user/logs/all',[UserController::class,'alllogs'])->middleware('auth.admin');
Route::post('/user/logs',[UserController::class,'logs'])->middleware('auth.admin');
Route::post('/user/usertimespent',[UserController::class,'usertimespent'])->middleware('auth.admin');
Route::post('/user/useralltimeview',[UserController::class,'userAllTimeView'])->middleware('auth.admin');
Route::post('/user/userdaytimeviewlist',[UserController::class,'userDayTimeViewList'])->middleware('auth.admin');
Route::post('/user/LastSeventyTwoViewsGraph',[UserController::class,'LastSeventyTwoViewsGraph'])->middleware('auth.admin');
Route::post('/user/LastTweentyFourViewsGraph',[UserController::class,'LastTweentyFourViewsGraph'])->middleware('auth.admin');
Route::post('/user/last24WatchingData',[UserController::class,'last24WatchingData'])->middleware('auth.admin');
Route::post('/user/last72WatchingData',[UserController::class,'last72WatchingData'])->middleware('auth.admin');
Route::get('/getuserlist',[UserController::class,'getallList'])->middleware('auth.admin');
Route::post('/user/userinfo',[UserController::class,'user_info'])->middleware('auth.admin');
//
Route::post('/user/userdefined/usertimespent',[UserController::class,'usertimespent2'])->middleware('auth.admin');

///////////END//////////////

//////////Live Channels/////////
Route::post('/livechannel/activechannellistgraph',[LiveChannelController::class,'activechannellistgraph'])->middleware('auth.admin.user');

///////////END////////////////

//////////Channels////////////
Route::get('trend/channels', [ChannelController::class, 'trendchannel'])->middleware('auth.admin.user');
Route::post('trend/reach/zero', [ChannelController::class, 'reachtrend'])->middleware('auth.admin.user');
Route::post('trend/reach/percent', [ChannelController::class, 'reachpercenttrend'])->middleware('auth.admin.user');
Route::post('trend/tvr/percent', [ChannelController::class, 'tvrtrend'])->middleware('auth.admin.user');
Route::post('trend/tvr/zero', [ChannelController::class, 'tvrtrendzero'])->middleware('auth.admin.user');
Route::post('channel/reach/percent', [ChannelController::class, 'reachpercent'])->middleware('auth.admin.user');
Route::post('channel/definedtrendreachp', [ChannelController::class, 'definedtrendreachp'])->middleware('auth.admin.user');
Route::post('channel/definedtrendreachp', [ChannelController::class, 'definedtrendreachp'])->middleware('auth.admin.user');
Route::post('channel/definedtrendreach0', [ChannelController::class, 'definedtrendreach0'])->middleware('auth.admin.user');
Route::post('channel/definedtrendtvrp', [ChannelController::class, 'definedtrendtvrp'])->middleware('auth.admin.user');
Route::post('channel/definedtrendtvr0', [ChannelController::class, 'definedtrendtvr0'])->middleware('auth.admin.user');
//////////END/////////////////


//////////////trend////////////////////


Route::get('servertime', [DeviceController::class, 'servertime']);

Route::post('channel/rangedtrendreach0', [TrendController::class, 'rangedtrendreach0']);
Route::post('trend/dayrangedreach0', [TrendController::class, 'dayrangedtrendreach0']);
Route::post('trend/dayrangedtvr0', [TrendController::class, 'dayrangedtrendtvr0']);
Route::post('trend/dayrangedtvrp', [TrendController::class, 'dayrangedtrendtvrp']);
Route::post('trend/dayrangedreachp', [TrendController::class, 'dayrangedtrendreachp']);
Route::get('demov', [TrendController::class, 'views']);
Route::get('demot', [TrendController::class, 'timeviewed']);
Route::post('channel/rangedtrendreachp', [TrendController::class, 'rangedtrendreachp']);
Route::post('channel/rangedtrendtvr0', [TrendController::class, 'rangedtrendtvr0']);
Route::post('channel/rangedtrendtvrp', [TrendController::class, 'rangedtrendtvrp']);

//////////////trend////////////////////


Route::post('/appuser/changepass', [AppUserController::class, 'changepass'])->middleware('auth.admin.user.deployer');
Route::post('/deployer/create',[AppUserController::class,'addDeployer']);
//Tanvir APIs//
Route::post('/appuser/create',[AppUserController::class,'store']);
Route::post('/appuser/edit',[AppUserController::class,'edit'])->middleware('auth.admin');
Route::post('/appuser/delete',[AppUserController::class,'delete'])->middleware('auth.admin');
Route::any('/appuser/activate',[AppUserController::class,'activateDeactivate'])->middleware('auth.admin');
Route::get('/appuser/list',[AppUserController::class,'list'])->middleware('auth.admin');
Route::get('/appuser/get/{user_name}',[AppUserController::class,'get'])->middleware('auth.admin');
Route::get('/logout',[AuthController::class,'logout'])->middleware('auth.admin.user.deployer');

Route::get('/receive',[RequestController::class,'receive']);
Route::post('/receiveoutside',[RequestController::class,'receiveoutside']);

//////////END/////////////////

Route::get("/test",[UserController::class,'demo_test']);