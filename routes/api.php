<?php

use App\Http\Controllers\V1\AboutUsController;
use App\Http\Controllers\V1\Admin\CategoryController;
use App\Http\Controllers\V1\QuestionsController;
use App\Http\Controllers\V1\OptionsController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\ContactUsController;
use App\Http\Controllers\V1\ImprintController;
use App\Http\Controllers\V1\PrivacyPolicyController;
use App\Http\Controllers\V1\ResultsController;
use App\Http\Controllers\V1\User\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::prefix('v1')->group(function () {

    Route::get('/config-clear', function () {
        Artisan::call('config:clear');
        return response()->json(['message' => 'Config cache cleared successfully.']);
    })->name('config.clear');
    Route::get('/optimize', function () {
        Artisan::call('optimize');
        return response()->json(['message' => 'Application optimized successfully.']);
    })->name('optimize');

    Route::post("/auth/login", [AuthController::class, 'login']);
    Route::post('/createuser', [AuthController::class, 'createAnonymousUser']);
    Route::get("/fetchquestions", [QuestionsController::class, 'fetchquestions']);
    Route::get("/fetchoptions", [OptionsController::class, 'fetchoptions']);

    Route::post('/savecontactus', [ContactUsController::class, 'savecontactus']);
    Route::post('/fetchcontactus', [ContactUsController::class, 'fetchcontactus']);
    Route::post('/saveaboutus', [AboutUsController::class, 'createOrUpdateAboutUs']);
    Route::post('/fetchaboutus', [AboutUsController::class, 'fetchAboutUs']);

    Route::post('/saveprivacypolicy', [PrivacyPolicyController::class, 'createOrUpdatePrivacyPolicy']);
    Route::post('/fetchprivacypolicy', [PrivacyPolicyController::class, 'fetchPrivacyPolicy']);


    Route::post('/saveimprint', [ImprintController::class, 'createOrUpdateImprint']);
    Route::post('/fetchimprint', [ImprintController::class, 'fetchImprint']);



    Route::post('/fetchresults', [ResultsController::class, 'fetchUserReport']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::post('/savecontactus', [ContactUsController::class, 'savecontactus']);
            Route::post('/fetchcontactus', [ContactUsController::class, 'getcontactus']);
            Route::post('/saveaboutus', [AboutUsController::class, 'createOrUpdateAboutUs']);
            Route::post('/fetchaboutus', [AboutUsController::class, 'fetchAboutUs']);
            Route::post('/savecategory', [CategoryController::class, 'saveCategory']);
            Route::post('/updatecategory', [CategoryController::class, 'updateCategory']);
            Route::post('/deletecategory', [CategoryController::class, 'deleteCategory']);
            Route::get('/fetchallcategories', [CategoryController::class, 'fetchAllCategories']);
            Route::post('/fetchcategorybyid', [CategoryController::class, 'fetchCategoryById']);
        });

        Route::prefix('user')->group(function () {
            Route::post('/saveresponses', [QuizController::class, 'saveResponses']);
        });


        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
