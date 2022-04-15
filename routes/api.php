<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/


Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'user'

], function ($router) {
   
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');
    Route::get('logout', 'UserController@logout');
    Route::get('refresh', 'UserController@refresh');
    Route::get('me/{loginId}', 'UserController@me');
    Route::get('profile/{loginId}', 'UserController@profile');
    Route::post('update/{loginId}', 'UserController@update');
    //Route::get('delete', 'UserController@delete');
});
  /*
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'category'

], function ($router) {
    //Category

   Route::get('getlist', 'CategoryController@get_categories');
    Route::get('get/{categoryId}', 'CategoryController@get_category');
    Route::post('create', 'CategoryController@create');
    Route::put('update/{categoryId}', 'CategoryController@update');
    Route::delete('delete/{categoryId}', 'CategoryController@delete');
    
});
*/

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'gallery'

], function ($router) {

    //Gallery
    Route::get('{loginId}/getlist', 'GalleryController@get_gallery_images');
    Route::post('{loginId}/upload', 'GalleryController@upload');
    Route::post('{loginId}/delete', 'GalleryController@delete');
    Route::get('{loginId}/image/', 'GalleryController@get_image'); 
});



Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'article'

], function ($router) {

    //Article 
    Route::get('{loginId}/getlist', 'ArticleController@get_articles');
    Route::get('{loginId}/get/{articleId}', 'ArticleController@get_article');
    // Route::get('getlist/{categoryId}', 'ArticleController@get_category_articles');
    //Route::get('search', 'ArticleController@search_article');
    Route::post('{loginId}/create', 'ArticleController@create');
    Route::post('{loginId}/update', 'ArticleController@update');
    Route::get('{loginId}/delete/{articleId}', 'ArticleController@delete');
    Route::post('{loginId}/deleteimage', 'ArticleController@deleteImage');
    Route::post('{loginId}/like', 'ArticleController@likes');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'comment'

], function ($router) {
    //Comment
    Route::get('{loginId}/getlist/{articleId}', 'CommentController@get_comments');
    Route::get('{loginId}/get/{commentId}', 'CommentController@get_comment');
    Route::post('{loginId}/create', 'CommentController@create');
    Route::post('{loginId}/reply', 'CommentController@create');
    Route::post('{loginId}/update', 'CommentController@update');
    Route::get('{loginId}/delete/{commentId}', 'CommentController@delete');
});

