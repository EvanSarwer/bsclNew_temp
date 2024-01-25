<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\PlayoutController;
use App\Http\Controllers\AdTrpController;
use App\Http\Controllers\DayPartsController;
use App\Http\Controllers\DayRangedController;
use App\Http\Controllers\DataCleanseController;
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





Route::get("/data/cleanse/alldates",[DataCleanseController::class,'index'])->name('data.cleanse.alldates');
Route::post('/overview/reachusergraph',[OverviewController::class,'reachusergraph']);
Route::post('/overview/reachpercentgraph',[OverviewController::class,'reachpercentgraph']);
Route::post('/overview/tvrgraphallchannelzero',[OverviewController::class,'tvrgraphallchannelzero']);
Route::post('/overview/tvrgraphallchannelpercent',[OverviewController::class,'tvrgraphallchannelpercent']);
Route::post('/overview/tvrsharegraph',[OverviewController::class,'tvrsharegraph']);
Route::post('/overview/timespentgraph',[OverviewController::class,'timespentgraph']);