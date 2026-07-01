<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\App;
use App\Http\Controllers\Super;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ─── Super admin ──────────────────────────────────────────────────────────────
Route::prefix('super')->name('super.')->group(function () {
    Route::middleware('guest:super')->group(function () {
        Route::get('login', [Super\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Super\AuthController::class, 'login']);
    });

    Route::middleware('auth:super')->group(function () {
        Route::post('logout', [Super\AuthController::class, 'logout'])->name('logout');
        Route::get('organizations', [Super\OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('organizations', [Super\OrganizationController::class, 'store'])->name('organizations.store');
        Route::put('organizations/{organization}', [Super\OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('organizations/{organization}', [Super\OrganizationController::class, 'destroy'])->name('organizations.destroy');

        Route::get('admins', [Super\AdminAccountController::class, 'index'])->name('admins.index');
        Route::post('admins', [Super\AdminAccountController::class, 'store'])->name('admins.store');
        Route::put('admins/{admin}', [Super\AdminAccountController::class, 'update'])->name('admins.update');
        Route::patch('admins/{admin}/toggle-active', [Super\AdminAccountController::class, 'toggleActive'])->name('admins.toggle-active');
        Route::delete('admins/{admin}', [Super\AdminAccountController::class, 'destroy'])->name('admins.destroy');
    });
});

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Participants
        Route::get('participants', [Admin\ParticipantController::class, 'index'])->name('participants.index');
        Route::post('participants', [Admin\ParticipantController::class, 'store'])->name('participants.store');
        Route::put('participants/{id}', [Admin\ParticipantController::class, 'update'])->name('participants.update');
        Route::patch('participants/{id}/toggle-active', [Admin\ParticipantController::class, 'toggleActive'])->name('participants.toggle-active');
        Route::delete('participants/{id}', [Admin\ParticipantController::class, 'destroy'])->name('participants.destroy');

        // Surveys
        Route::get('surveys', [Admin\SurveyController::class, 'index'])->name('surveys.index');
        Route::post('surveys', [Admin\SurveyController::class, 'store'])->name('surveys.store');
        Route::put('surveys/{id}', [Admin\SurveyController::class, 'update'])->name('surveys.update');
        Route::post('surveys/{id}/publish', [Admin\SurveyController::class, 'publish'])->name('surveys.publish');
        Route::delete('surveys/{id}', [Admin\SurveyController::class, 'destroy'])->name('surveys.destroy');
        Route::post('surveys/{id}/sections', [Admin\SurveyController::class, 'storeSection'])->name('surveys.sections.store');
        Route::delete('surveys/{survey}/sections/{section}', [Admin\SurveyController::class, 'destroySection'])->name('surveys.sections.destroy');
        Route::post('surveys/{id}/questions', [Admin\SurveyController::class, 'storeQuestion'])->name('surveys.questions.store');
        Route::put('surveys/{survey}/questions/{question}', [Admin\SurveyController::class, 'updateQuestion'])->name('surveys.questions.update');
        Route::delete('surveys/{survey}/questions/{question}', [Admin\SurveyController::class, 'destroyQuestion'])->name('surveys.questions.destroy');
        Route::post('surveys/{id}/reorder', [Admin\SurveyController::class, 'reorder'])->name('surveys.reorder');
        Route::get('surveys/{id}/dashboard', [Admin\DashboardController::class, 'survey'])->name('surveys.dashboard');

        // Announcements
        Route::get('announcements', [Admin\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('announcements', [Admin\AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('announcements/{id}', [Admin\AnnouncementController::class, 'update'])->name('announcements.update');
        Route::patch('announcements/{id}/toggle-status', [Admin\AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
        Route::delete('announcements/{id}', [Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });
});

// ─── App (Participant) ────────────────────────────────────────────────────────
Route::prefix('app')->name('app.')->group(function () {
    Route::middleware('guest:participant')->group(function () {
        Route::get('login', [App\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [App\AuthController::class, 'login']);
    });

    Route::middleware('auth:participant')->group(function () {
        Route::post('logout', [App\AuthController::class, 'logout'])->name('logout');

        // Surveys
        Route::get('surveys', [App\SurveyResponseController::class, 'index'])->name('surveys.index');
        Route::get('surveys/{id}', [App\SurveyResponseController::class, 'show'])->name('surveys.show');
        Route::post('surveys/{id}/save', [App\SurveyResponseController::class, 'save'])->name('surveys.save');
        Route::post('surveys/{id}/submit', [App\SurveyResponseController::class, 'submit'])->name('surveys.submit');

        // Announcements
        Route::get('announcements', [App\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{id}', [App\AnnouncementController::class, 'show'])->name('announcements.show');
    });
});
