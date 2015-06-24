<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
//print App::environment();
/*Route::get('/', function()
{
	$images = scandir("../app/start");
	print_r($images);
});*/
Route::get('/', 'HomeController@showWelcome');
Route::controller('dfd','TicketsController');