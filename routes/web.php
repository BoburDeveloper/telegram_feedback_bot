<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegrambot;
use App\Http\middleware\BasicAuth;



Route::get('/', ['uses'=>'App\Http\Controllers\Telegrambot@index'])->middleware(BasicAuth::class);


Route::any('/form', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}/{id}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);