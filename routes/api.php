<?php

use App\Http\Controllers\V1\AboutUsController;
use App\Http\Controllers\V1\Admin\CategoryController;
use App\Http\Controllers\V1\Admin\StatisticsController;
use App\Http\Controllers\V1\Admin\UserController;
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
    Route::get('/sanctum/csrf-cookie', function (Request $request) {
        return response()->json(['message' => 'CSRF cookie set']);
    });

    Route::post("/auth/login", [AuthController::class, 'login']);
    Route::post('/create-user', [AuthController::class, 'createAnonymousUser']);
    Route::get("/fetch-questions", [QuestionsController::class, 'fetchQuestions']);
    Route::get("/fetch-options", [OptionsController::class, 'fetchOptions']);

    Route::post('/save-contactus', [ContactUsController::class, 'saveContactUs']);
    Route::post('/fetch-contactus', [ContactUsController::class, 'fetchContactUs']);
    Route::post('/save-aboutus', [AboutUsController::class, 'createOrUpdateAboutUs']);
    Route::post('/fetch-aboutus', [AboutUsController::class, 'fetchAboutUs']);

    Route::post('/save-privacypolicy', [PrivacyPolicyController::class, 'createOrUpdatePrivacyPolicy']);
    Route::post('/fetch-privacypolicy', [PrivacyPolicyController::class, 'fetchPrivacyPolicy']);


    Route::post('/save-imprint', [ImprintController::class, 'createOrUpdateImprint']);
    Route::post('/fetch-imprint', [ImprintController::class, 'fetchImprint']);



    Route::post('/fetch-results', [ResultsController::class, 'fetchUserReport']);
    Route::post('/download-report', [ResultsController::class, 'downloadReport']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::post('/save-contactus', [ContactUsController::class, 'saveContactUs']);
            Route::post('/fetch-contactus', [ContactUsController::class, 'fetchContactUs']);
            Route::post('/save-aboutus', [AboutUsController::class, 'createOrUpdateAboutUs']);
            Route::post('/fetch-aboutus', [AboutUsController::class, 'fetchAboutUs']);
            Route::post('/save-category', [CategoryController::class, 'saveCategory']);
            Route::post('/update-category', [CategoryController::class, 'updateCategory']);
            Route::post('/delete-category', [CategoryController::class, 'deleteCategory']);
            Route::get('/fetch-all-categories', [CategoryController::class, 'fetchAllCategories']);
            Route::post('/fetch-categorybyid', [CategoryController::class, 'fetchCategoryById']);
            Route::post('/fetch-users', [UserController::class, 'fetchUsers']);
            Route::post('/fetch-results', [ResultsController::class, 'fetchUserReport']);
            Route::post('/fetch-statistics', [StatisticsController::class, 'fetchStatistics']);

        });

        Route::prefix('user')->group(function () {
            Route::post('/save-responses', [QuizController::class, 'saveResponses']);
        });


        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
