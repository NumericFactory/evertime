<?php

/*
  |--------------------------------------------------------------------------
  | Routes File
  |--------------------------------------------------------------------------
  |
  | Here is where you will register all of the routes in an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */
/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | This route group applies the "web" middleware group to every route
  | it contains. The "web" middleware group is defined in your HTTP
  | kernel and includes session state, CSRF protection, and more.
  |
 */

Route::group(['middleware' => ['web']], function () {
    // get evergreen deadline for timer & email
    Route::get('api/{timer}/getdeadline', 'TimerController@api_getdeadline');

    // canceled
    Route::get('canceled', [
        'as' => 'users.canceled',
        'uses' => 'UserController@canceled'
    ]);

    // login
    Route::get('/', 'Auth\AuthController@getLogin');

    // webfont
    Route::get('wf/{timer}', [
        'as' => 'timer.webfont',
        'uses' => 'TimerController@webfont'
    ]);

    // image timer
    Route::get('st/{timer}', [
        'as' => 'timer.image',
        'uses' => 'TimerController@image'
    ]);

    // js embedded timer
    Route::get('jst/{timer}', [
        'as' => 'timer.js',
        'uses' => 'TimerController@javascript'
    ]);

    // timer link
    Route::get('lst/{timer}', [
        'as' => 'timer.link',
        'uses' => 'TimerController@link'
    ]);

    // Stripe webhook
    Route::post('stwh', [
        'uses' => 'UserController@stripe'
    ]);

    // Route::get('generate/{style}', [
    //     'as' => 'timer.syle',
    //     'uses' => 'TimerController@generateStyle'
    // ]);
});

Route::group(['middleware' => ['web', 'checkRegisterRoute']], function () {
    Route::auth();
});

Route::group(['middleware' => ['web', 'auth', 'inactive']], function () {

    // index
    Route::get('timers', [
        'as' => 'timers.index',
        'uses' => 'TimerController@index'
    ]);

    // preview
    Route::get('preview', [
        'as' => 'timer.preview',
        'uses' => 'TimerController@preview'
    ]);

    Route::get('timer/create', [
        'middleware' => 'checkTimersCount',
        'as' => 'timer.create',
        'uses' => 'TimerController@create'
    ]);

    Route::get('timer/{timer}', [
        'as' => 'timer.update',
        'uses' => 'TimerController@update'
    ]);

    Route::post('timer', [
        'as' => 'timer.store',
        'uses' => 'TimerController@store'
    ]);

    Route::patch('timer/{timer}', [
        'as' => 'timer.save.update',
        'uses' => 'TimerController@store'
    ]);

    Route::delete('timer/{timer}', [
        'as' => 'timer.delete',
        'uses' => 'TimerController@destroy'
    ]);

    Route::get('embed/{timer}', [
        'as' => 'timer.embed',
        'uses' => 'TimerController@embed'
    ]);

    Route::controllers([
        'settings' => 'SettingsController'
    ]);
});
