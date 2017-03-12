<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::resource('users', 'UserController');

Route::resource('roles', 'RolesController');

Route::post('/password/securityquestions', 'Auth\PasswordController@getPasswordSecurityQuestions');

Route::post('/resetPassword', 'Auth\PasswordController@resetPassword');

Route::post('/passwordchanged', 'Auth\PasswordController@changePassword');

Route::get('/home', 'HomeController@index');

Route::resource('emergencies', 'EmergencyController');

Route::resource('patients', 'PatientController');
