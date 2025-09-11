<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayinController;
use App\Http\Controllers\PayinWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'UserController@loginpage')->middleware('guest')->name('mylogin');
Route::post('/update-theme-mode', 'UserController@updateThemeMode')->name('update.theme.mode');

Route::group(['prefix' => 'auth'], function () {
    Route::post('check', 'UserController@login')->name('authCheck');
    Route::get('logout', 'UserController@logout')->name('logout');
    Route::post('reset', 'UserController@passwordReset')->name('authReset');
    Route::post('register', 'UserController@registration')->name('register');
    Route::post('getotp', 'UserController@getotp')->name('getotp');
    Route::post('setpin', 'UserController@setpin')->name('setpin');
    Route::post('ekyc', 'UserController@ekyc')->name('ekyc');
    Route::get('payoutClear', 'CronController@payoutProcessClear');
    Route::get('routechache', 'UserController@routechache');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', 'HomeController@index')->middleware("company")->name('home');
    Route::get('complete/profile', 'MemberController@completeProfile')->name('memberkyc');
    Route::post('wallet/balance', 'HomeController@getbalance')->name('getbalance');
    Route::get('setpermissions', 'HomeController@setpermissions');
    Route::get('setscheme', 'HomeController@setscheme');
    Route::get('getmyip', 'HomeController@getmysendip');
    Route::get('mydata', 'HomeController@mydata');
    Route::post('mystatics', 'HomeController@statics')->name("mystatics");
    Route::get('/piget-custom-data', 'HomeController@pigetCustomData')->name('piget_custom_data');
    Route::get('/poget-custom-data', 'HomeController@pogetCustomData')->name('poget_custom_data');
    Route::post('mycommission', 'HomeController@mycommission')->name("mycommission");
    Route::post('useronboard', 'HomeController@useronboard')->name("useronboard");
    Route::get('checkcommission', 'HomeController@checkcommission');
    Route::group(['prefix' => 'tools', 'middleware' => ['company']], function () {
        Route::get('{type}', 'RoleController@index')->name('tools');
        Route::post('{type}/store', 'RoleController@store')->name('toolsstore');
        Route::post('setpermissions', 'RoleController@assignPermissions')->name('toolssetpermission');
        Route::post('updatepermissions', 'RoleController@setPermissions')->name('toolsupdatepermission');
        Route::post('get/permission/{id}', 'RoleController@getpermissions')->name('permissions');
        Route::post('getdefault/permission/{id}', 'RoleController@getdefaultpermissions')->name('defaultpermissions');
    });

    /* Reporting & Actions */
    Route::group(['prefix' => 'statement', 'middleware' => ['company']], function () {
        Route::get('report/{type?}/{id?}', 'Report\ReportController@index')->name('reports');
        Route::post('report/static', 'Report\ReportController@fetchData')->name('reportstatic');
        Route::post('list/fetch/{type}/{id?}/{returntype?}', 'Report\CommonController@fetchData');
    });

    Route::group(['prefix' => 'export', 'middleware' => ['company']], function () {
        Route::get('report/{type}', 'Report\ExportController@export');
    });

    Route::group(['prefix' => 'report/action', 'middleware' => 'service'], function () {
        Route::post('update', 'ActionController@update')->name('statementUpdate');
        Route::post('status', 'ActionController@status')->name('statementStatus');
        Route::post('delete', 'ActionController@delete')->name('statementDelete');
    });

    Route::group(['prefix' => 'member', 'middleware' => ['company']], function () {
        Route::get('{type}/{action?}', 'MemberController@index')->name('member');
        Route::post('store', 'MemberController@create')->name('memberstore')->middleware('mpin');
        Route::post('commission/update', 'MemberController@commissionUpdate')->name('commissionUpdate');
        Route::post('getcommission', 'MemberController@getCommission')->name('getMemberCommission');
        Route::post('getpackagecommission', 'MemberController@getPackageCommission')->name('getMemberPackageCommission');
    });

    Route::group(['prefix' => 'portal', 'middleware' => ['company']], function () {
        Route::get('{type}', 'PortalController@index')->name('portal');
        Route::post('store', 'PortalController@create')->name('portalstore');
    });

    Route::group(['prefix' => 'logs', 'middleware' => ['company']], function () {
        Route::get('{type}', 'PortalController@logs')->name('portallogs');
    });

    Route::group(['prefix' => 'fund', 'middleware' => ['company']], function () {
        Route::get('{type}/{action?}', 'FundController@index')->name('fund');
        Route::post('transaction', 'FundController@transaction')->name('fundtransaction')->middleware('mpin');
    });

    Route::group(['prefix' => 'payout', 'middleware' => ['company', 'service']], function () {
        Route::post('transaction', 'PayoutController@payment')->name('payout')->middleware("balanceCheck");
    });

    Route::group(['prefix' => 'qrtest', 'middleware' => ['company', 'service']], function () {
        Route::post('transaction', 'Services\CollectionController@qrtest')->name('qrtest');
    });

    Route::group(['prefix' => 'profile', 'middleware' => ['auth']], function () {
        Route::get('/view/{id?}', 'SettingController@index')->name('profile');
        Route::post('update', 'SettingController@profileUpdate')->name('profileUpdate')->middleware('mpin');
    });

    Route::group(['prefix' => 'setup', 'middleware' => ['company']], function () {
        Route::get('{type}/{id?}', 'SetupController@index')->name('setup');
        Route::post('update', 'SetupController@update')->name('setupupdate');
    });

    Route::group(['prefix' => 'resources', 'middleware' => ['company']], function () {
        Route::get('{type}', 'ResourceController@index')->name('resource');
        Route::post('update', 'ResourceController@update')->name('resourceupdate');
        Route::post('get/{type}/commission', 'ResourceController@getCommission');
        Route::post('get/{type}/packagecommission', 'ResourceController@getPackageCommission');
    });

    Route::group(['prefix' => 'apiswitch', 'middleware' => ['company']], function () {
        Route::get('{type}/{id?}', 'ApiSwitchController@index')->name('apiswitch');
        Route::post('update', 'ApiSwitchController@update')->name('apiswitchupdate');
    });

    Route::group(['prefix' => 'developer/api', 'middleware' => ['company', 'checkrole:apiuser']], function () {
        Route::get('{type}', 'ApiController@index')->name('apisetup');
        Route::post('update', 'ApiController@update')->name('apitokenstore');
        Route::post('token/delete', 'ApiController@tokenDelete')->name('tokenDelete');
    });

    Route::group(['prefix' => 'complaint', 'middleware' => ['company']], function () {
        Route::get('/', 'ComplaintController@index')->name('complaint');
        Route::get('/list', 'ComplaintController@index')->name('complaintlist');
        Route::post('store', 'ComplaintController@store')->name('complaintstore');
    });

    Route::get('commission', 'HomeController@checkcommission');
});

Route::post('/payin/intent', [PayinController::class, 'createIntent']);
Route::get('/payin/status/{txnid}', [PayinController::class, 'status']);
Route::post('/webhook/payin', [PayinWebhookController::class, 'handle']);
Route::get('/pay', [PayinController::class, 'showForm'])->name('pay.form');
