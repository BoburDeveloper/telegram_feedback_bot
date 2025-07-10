<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegrambot;



Route::get('/', ['uses'=>'App\Http\Controllers\Telegrambot@index']);


Route::any('/form', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}/{id}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);