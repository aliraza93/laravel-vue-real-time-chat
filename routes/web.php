<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Chat
Route::prefix('messenger')->name('messenger')->group(function () {
    Route::get('/', [MessageController::class, 'index']);
    Route::get('users', [MessageController::class, 'fetchUsers']);
    Route::get('user/{id}', [MessageController::class, 'fetchUser']);
    Route::get('private-messages/{user}', [MessageController::class, 'privateMessages'])->name('privateMessages');
    Route::post('private-messages/{user}', [MessageController::class, 'sendPrivateMessage'])->name('privateMessages.store');
    Route::post('send-media', [MessageController::class, 'sendMedia']);
    Route::post('seen-messages/{user}', [MessageController::class, 'seenPrivateMessages']);
    Route::delete('delete-private-messages/{user}', [MessageController::class, 'deletePrivateMessage']);
    Route::get('active-friend/{user}', [MessageController::class, 'getActiveFriend']);
    Route::get('update-last-seen/{user}', [MessageController::class, 'updateLastSeen']);
    Route::post('message-delivered/{message}', [MessageController::class, 'updateMessageDeliveredStatuss']);
    Route::get('download-message-file/{media}', [MessageController::class, 'downloadFile']);
});