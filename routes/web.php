<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MultiplicationGame;
use App\Livewire\TimesTableGame;

Route::get('/', function () {
    return redirect()->route('pop-quiz');
});

Route::get('/pop-quiz', MultiplicationGame::class)->name('pop-quiz');
Route::get('/times-tables', TimesTableGame::class)->name('times-tables');
