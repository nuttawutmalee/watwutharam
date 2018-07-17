<?php

if (config('cms-client.mockup.mode') == false || strtolower(config('cms-client.mockup.mode')) == 'false') {
    Route::get('/system/{any?}', 'AngularController@index');

    Route::group(['middleware' => ['cms']], function () {
        Route::get('/{language_code}/sitemap.xml', 'CMSController@getSiteMapXML');
        Route::get('sitemap.xml', 'CMSController@getMainSiteMapXML');
        Route::get('{name?}', 'CMSController@index');
    });
} else {
    Route::group(['middleware' => ['cms-mockup']], function () {
        Route::get('{name?}', 'MockUpController@index');
    });
}
