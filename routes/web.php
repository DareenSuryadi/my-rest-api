<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/art', function () {
    return view('pages.plp');
})->name('plp');

Route::get('/art/{i}', function () {
    return view('pages.pdp');
})->name('pdp');