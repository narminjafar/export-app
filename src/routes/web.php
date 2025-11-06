<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/error-test', function() {
    abort(401, 'Token expired');
});
