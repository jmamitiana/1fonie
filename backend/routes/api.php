<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/auth/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/auth/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword']);

// Public mission routes
Route::get('/missions', [App\Http\Controllers\Api\MissionController::class, 'index']);
Route::get('/missions/{mission}', [App\Http\Controllers\Api\MissionController::class, 'show']);
Route::get('/categories', [App\Http\Controllers\Api\MissionController::class, 'categories']);
Route::get('/cities', [App\Http\Controllers\Api\MissionController::class, 'cities']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::put('/auth/profile', [App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [App\Http\Controllers\Api\AuthController::class, 'updatePassword']);

    // Company routes
    Route::get('/company/profile', [App\Http\Controllers\Api\CompanyController::class, 'profile']);
    Route::put('/company/profile', [App\Http\Controllers\Api\CompanyController::class, 'updateProfile']);
    Route::get('/company/missions', [App\Http\Controllers\Api\CompanyController::class, 'missions']);
    Route::post('/company/missions', [App\Http\Controllers\Api\CompanyController::class, 'createMission']);
    Route::get('/company/missions/{mission}', [App\Http\Controllers\Api\CompanyController::class, 'showMission']);
    Route::put('/company/missions/{mission}', [App\Http\Controllers\Api\CompanyController::class, 'updateMission']);
    Route::delete('/company/missions/{mission}', [App\Http\Controllers\Api\CompanyController::class, 'deleteMission']);
    Route::get('/company/missions/{mission}/applications', [App\Http\Controllers\Api\CompanyController::class, 'missionApplications']);
    Route::post('/company/missions/{mission}/select-provider', [App\Http\Controllers\Api\CompanyController::class, 'selectProvider']);
    Route::post('/company/missions/{mission}/pay', [App\Http\Controllers\Api\CompanyController::class, 'payMission']);
    Route::post('/company/missions/{mission}/complete', [App\Http\Controllers\Api\CompanyController::class, 'completeMission']);
    Route::get('/company/payments', [App\Http\Controllers\Api\CompanyController::class, 'payments']);
    Route::get('/company/dashboard', [App\Http\Controllers\Api\CompanyController::class, 'dashboard']);

    // Provider routes
    Route::get('/provider/profile', [App\Http\Controllers\Api\ProviderController::class, 'profile']);
    Route::put('/provider/profile', [App\Http\Controllers\Api\ProviderController::class, 'updateProfile']);
    Route::post('/provider/stripe-connect', [App\Http\Controllers\Api\ProviderController::class, 'connectStripe']);
    Route::get('/provider/missions', [App\Http\Controllers\Api\ProviderController::class, 'missions']);
    Route::get('/provider/missions/available', [App\Http\Controllers\Api\ProviderController::class, 'availableMissions']);
    Route::get('/provider/missions/{mission}', [App\Http\Controllers\Api\ProviderController::class, 'showMission']);
    Route::post('/provider/missions/{mission}/apply', [App\Http\Controllers\Api\ProviderController::class, 'applyMission']);
    Route::post('/provider/missions/{mission}/withdraw', [App\Http\Controllers\Api\ProviderController::class, 'withdrawApplication']);
    Route::get('/provider/applications', [App\Http\Controllers\Api\ProviderController::class, 'applications']);
    Route::get('/provider/earnings', [App\Http\Controllers\Api\ProviderController::class, 'earnings']);
    Route::get('/provider/dashboard', [App\Http\Controllers\Api\ProviderController::class, 'dashboard']);
    Route::put('/provider/availability', [App\Http\Controllers\Api\ProviderController::class, 'toggleAvailability']);

    // Messaging routes
    Route::get('/messages/{mission}', [App\Http\Controllers\Api\MessageController::class, 'index']);
    Route::post('/messages/{mission}', [App\Http\Controllers\Api\MessageController::class, 'store']);
    Route::put('/messages/{message}/read', [App\Http\Controllers\Api\MessageController::class, 'markAsRead']);

    // Review routes
    Route::post('/missions/{mission}/reviews', [App\Http\Controllers\Api\ReviewController::class, 'store']);
    Route::get('/providers/{provider}/reviews', [App\Http\Controllers\Api\ReviewController::class, 'providerReviews']);
    Route::get('/companies/{company}/reviews', [App\Http\Controllers\Api\ReviewController::class, 'companyReviews']);

    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);

    // Payment routes
    Route::post('/payments/{mission}/create-intent', [App\Http\Controllers\Api\PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/webhook', [App\Http\Controllers\Api\PaymentController::class, 'webhook']);
    Route::get('/payments/{payment}', [App\Http\Controllers\Api\PaymentController::class, 'show']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Api\AdminController::class, 'dashboard']);
    Route::get('/admin/users', [App\Http\Controllers\Api\AdminController::class, 'users']);
    Route::put('/admin/users/{user}/toggle-active', [App\Http\Controllers\Api\AdminController::class, 'toggleUserActive']);
    Route::delete('/admin/users/{user}', [App\Http\Controllers\Api\AdminController::class, 'deleteUser']);
    Route::get('/admin/missions', [App\Http\Controllers\Api\AdminController::class, 'missions']);
    Route::put('/admin/missions/{mission}/status', [App\Http\Controllers\Api\AdminController::class, 'updateMissionStatus']);
    Route::get('/admin/payments', [App\Http\Controllers\Api\AdminController::class, 'payments']);
    Route::get('/admin/analytics', [App\Http\Controllers\Api\AdminController::class, 'analytics']);
    Route::get('/admin/disputes', [App\Http\Controllers\Api\AdminController::class, 'disputes']);
    Route::post('/admin/disputes/{dispute}/resolve', [App\Http\Controllers\Api\AdminController::class, 'resolveDispute']);
});
