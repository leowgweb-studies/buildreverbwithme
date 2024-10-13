<?php

use App\Livewire\Pages\Play;
use Illuminate\Support\Facades\Route;
// optional params
Route::view('/', 'home')->name('game.home');
Route::get('/play/{gameKey?}', Play::class)->name('game.play');


require __DIR__.'/auth.php';
