<?php

Route::post('/preview', 'CMSController@preview');
Route::post('/render-paginator', 'CMSController@renderPaginator');

Route::post('/quick-upload', 'CMSController@upload');
Route::post('/multiple-upload', 'CMSController@multipleUpload');
Route::get('/upload-dir', 'CMSController@listUploads');
Route::post('/rename', 'CMSController@renameFile');
Route::delete('/delete', 'CMSController@deleteFile');

Route::get('/clear-cache', 'CMSController@clearCache');

Route::post('/render-paginator', 'CMSController@renderPaginator');