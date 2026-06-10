<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\ProjectControllerV1;
use App\Http\Controllers\V1\ProjectLeadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('throttle:30,1')->group(function () {

    Route::get('/landing/budget-projects', [ProjectControllerV1::class, 'budgetSlider']);

    Route::get('/landing/2bhk-projects', [ProjectControllerV1::class, 'twoBhkProjects']);
    Route::get('/landing/3bhk-projects', [ProjectControllerV1::class, 'threeBhkProjects']);
    Route::get('/landing/4bhk-projects', [ProjectControllerV1::class, 'fourBhkProjects']);
    Route::get('/landing/5bhk-projects', [ProjectControllerV1::class, 'fiveBhkProjects']);

    Route::get('/project/{slug}', [ProjectControllerV1::class, 'projectDetails']);
    Route::get('project/{slug}/towers', [ProjectControllerV1::class, 'projectTowers']);
    Route::post('/project-lead', [ProjectLeadController::class, 'store']);
    Route::get('/map-projects', [ProjectControllerV1::class, 'mapProjects']);
});