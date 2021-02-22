<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/find/{id}', 'FriendController@find');
Route::post('/friend', 'FriendController@addFriend');
Route::get('/friend', 'HomeController@fetchAllFriends');
Route::post('/chat/{id}', 'ChatController@startChat');
Route::get('/chats', 'HomeController@fetchAllRecentChats');
Route::post('/send', 'ChatController@sendMessage');
Route::post('/clear', 'ChatController@clearChat');
Route::post('/unfriend', 'FriendController@unfriend');
Route::post('/clearall', 'ChatController@deleteAllChats');
Route::put('/read', 'HomeController@markAsRead');
Route::post('/friendreq/{id}', 'FriendController@sendFriendRequest');
Route::get('/notif', 'HomeController@fetchAllNotifications');
Route::post('/updateprofile', 'HomeController@updateProfile');
Route::delete('/deletechatroom', 'ChatController@deleteChatroom');