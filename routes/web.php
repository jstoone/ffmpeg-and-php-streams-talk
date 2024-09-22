<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Accepts a file upload and copies the file,
//   using stream_copy_to_stream, to a new location.
Route::post('/copy', function () {
    $filePath = request()->file('file')->path();

    dd($filePath);



//    $filePath->storeAs('uploads', $filePath->getClientOriginalName());
    return response()->json(['message' => 'File uploaded successfully']);
});
