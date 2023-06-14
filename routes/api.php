<?php

use App\Http\Controllers\CreateurController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticlesController;
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
// for user or particulier
Route::post("/register/user", [UserController::class, "createUser"]);
Route::post("/login/user", [UserController::class, "loginUser"]);
Route::put("/update/user/{idUser}", [UserController::class, "updateUser"]);
Route::get("/users", [UserController::class, "getUsers"]);
Route::get("/users/{idUser}", [UserController::class, "getUserById"]);
Route::delete("/delete/user/{idUser}", [UserController::class, "deleteUser"]);
Route::patch('/reset-password/{uid}', [UserController::class, 'resetPassword']);

// for createur or professionnel
Route::post("/register/createur", [CreateurController::class, "createCreateur"]);
Route::post("/login/createur", [CreateurController::class, "loginCreateur"]);
Route::get("/createurs/{idCreateur}", [CreateurController::class, "getCreateurById"]);
Route::get("/createurs", [CreateurController::class, "getCreateurs"]);
Route::put("/update/createur/{idCreateur}", [CreateurController::class, "updateCreateur"]);
Route::delete("/delete/createur/{idCreateur}", [CreateurController::class, "deleteCreateur"]);

// for articles
Route::get("/articles", [ArticlesController::class, "index"]);
Route::get("/articles/{idArticle}", [ArticlesController::class, "show"]);

// for research
Route::get('/articles', [ArticlesController::class, "searchByCategorie"]);

// for filter
Route::get('/filter', [ArticlesController::class, 'filter']);


Route::group(['middleware' => ['auth:sanctum', 'check.article.owner']], function () {
    Route::post("/articles", [ArticlesController::class, "store"]);
    Route::put("/articles/{idArticle}", [ArticlesController::class, "update"]);
    Route::delete("/articles/{idArticle}", [ArticlesController::class, "destroy"]);
});
