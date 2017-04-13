<?php

Route::group(['middleware' => ['web']], function() {
    Route::get('/test', ['as' => 'index', 'uses' => 'TelegramController@test']);
    Route::get('/', ['as' => 'index', 'uses' => 'SiteController@index']);
    Route::get('/settings', ['as' => 'settings', 'uses' => 'SiteController@settings']);
    Route::get('/vk/settings', ['as' => 'vk.settings', 'uses' => 'SiteController@vk']);
    Route::get('/settings/new', ['as' => 'new_settings', 'uses' => 'SiteController@settingsNew']);
    Route::get('/calendar', ['as' => 'calendar', 'uses' => 'SiteController@calendar']);
    Route::group(['prefix' => 'auth'], function() {
        Route::get('login', ['as' => 'login', 'uses' => 'VkAuthController@auth']);
    });
    Route::get('/logout', ['as' => 'logout', 'uses' => 'SiteController@logout']);
    Route::group(['middleware' => 'auth'], function() {
        Route::get('profile', ['as' => 'profile', 'uses' => 'SiteController@profile']);
    });

    Route::get('/html/{page}', function($page) {
        return view('page.' . $page);
    });

    Route::get('/vk/image/{site}/{a}/{b}/{c}', function($site, $a, $b, $c) {
        $url = 'http://' . implode('/', func_get_args());
        if (!($data = Cache::get($url))) {
            $data = file_get_contents($url);
            Cache::put($url, $data, 60);
        }
        $response = Response::make($data, 200);
        $response
            ->header('Content-Type', 'image/jpeg')
            ->setTtl(600)
            ->setClientTtl(600)
            ->setMaxAge(600);

        return $response;
    });
});

Route::group(['prefix' => 'api'], function() {
    Route::get('generateToken', 'TelegramController@generateToken');
    Route::group(['prefix' => 'en'], function() {
        Route::get('games/{domain}', 'EncounterController@parse');
    });
    Route::group(['prefix' => 'lampa'], function() {
        Route::get('games/{domain}', 'Lampa@games');
        Route::get('commands/{domain}', 'Lampa@commands');
    });
});

Route::any('hook', 'TelegramController@newhook');
Route::any('newbot', 'TelegramController@newbot');
Route::get('setup', 'TelegramController@setup');
