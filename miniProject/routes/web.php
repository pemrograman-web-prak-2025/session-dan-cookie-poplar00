<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('register');
});

// Auth views
Route::get('/register', [PlayerController::class, 'register'])->name('register');
Route::post('/register', [PlayerController::class, 'validateRegist'])->name('regist.process');

Route::get('/login', [PlayerController::class, 'login'])->name('login');
Route::post('/login', [PlayerController::class, 'validateLogin'])->name('login.process');

Route::get('/logout', [PlayerController::class, 'logout'])->name('logout'); // sementara GET; idealnya POST

// Homepage (game + leaderboard)
Route::get('/homepage', [PlayerController::class, 'homepage'])->name('homepage');
Route::post('/game/play', [PlayerController::class, 'playRPS'])->name('game.play');

// CRUD routes for players (admin-style simple CRUD)
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create');
Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');
Route::delete('/account/delete', [PlayerController::class, 'deleteAccount'])
    ->middleware('auth')
    ->name('account.delete');
