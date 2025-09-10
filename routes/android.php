<?php

use Illuminate\Http\Request;

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
/*Android Auth Apis*/
Route::any('auth/slide', 'Android\UserController@slide');
Route::any('auth/v1', 'Android\UserController@login');
Route::any('auth/logout', 'Android\UserController@logout');

// Member Apis
Route::any('getbalance', 'Android\UserController@getbalance');
Route::any('changePassword', 'Android\UserController@changePassword');
Route::any('getcommission', 'Android\UserController@getcommission');
Route::any('getbalance', 'Android\UserController@getbalance');

// Transaction Report
Route::any('transaction', 'Report\ReportController@fetchData');
Route::any('aepsfund', 'Android\FundController@transaction');
Route::any('generateqr', 'Services\CollectionController@qrtest');
