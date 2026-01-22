<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MultiplicationGame;

Route::get('/', function () {
    return redirect()->route('game');
});

Route::get('/game', MultiplicationGame::class)->name('game');
