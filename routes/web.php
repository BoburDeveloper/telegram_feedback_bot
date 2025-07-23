<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telegrambot;
use App\Http\Controllers\VerificationController;


Route::get('/', ['uses'=>'App\Http\Controllers\Telegrambot@index']);


Route::any('/form', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);
Route::any('/form/{action}/{id}', ['uses'=>'App\Http\Controllers\Telegrambot@form']);

Route::post('/telegram/send-code',  ['uses'=>'App\Http\Controllers\Telegrambot@sendCode']);
Route::post('/telegram/verify-code',  ['uses'=>'App\Http\Controllers\Telegrambot@verifyCode']);