<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegrambot;



Route::post('/telegraph/{token}/handle', [Telegrambot::class, 'handle'])->name('telegraph.webhook');
