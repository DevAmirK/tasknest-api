<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

//Route::fallback(function () {
//    return response()->json([
//        'message' => 'Not Found.'
//    ], 404);
//});

Route::get('/', function () {
    dd(config('app.frontend_url'));
});
