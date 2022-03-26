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
    Route::get('me', 'UserController@me');
    Route::get('profile/{userId}', 'UserController@profile');
    Route::post('update', 'UserController@update');
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
    'prefix' => 'article'

], function ($router) {

    //Article 
    Route::get('getlist', 'ArticleController@get_articles');
    Route::get('get/{articleId}', 'ArticleController@get_article');
    // Route::get('getlist/{categoryId}', 'ArticleController@get_category_articles');
    //Route::get('search', 'ArticleController@search_article');
    Route::post('create', 'ArticleController@create');
    Route::post('update', 'ArticleController@update');
    Route::get('delete/{articleId}', 'ArticleController@delete');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'comment'

], function ($router) {
    //Comment
    Route::get('getlist/{articleId}', 'CommentController@get_comments');
    Route::get('get/{commentId}', 'CommentController@get_comment');
    Route::post('create', 'CommentController@create');
    Route::post('update', 'CommentController@update');
    Route::get('delete/{commentId}', 'CommentController@delete');
});

