<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoomTypeController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\BookingServiceController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\ReportController;

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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public room and service information
Route::get('/room-types', [RoomTypeController::class, 'index']);
Route::get('/room-types/{id}', [RoomTypeController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Customer routes
    Route::middleware('role:customer')->group(function () {
        // Bookings
        Route::get('/my-bookings', [BookingController::class, 'myBookings']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::put('/bookings/{id}', [BookingController::class, 'update']);
        Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
        
        // Booking services
        Route::post('/booking-services', [BookingServiceController::class, 'store']);
        Route::put('/booking-services/{id}', [BookingServiceController::class, 'update']);
        Route::delete('/booking-services/{id}', [BookingServiceController::class, 'destroy']);
        
        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    });
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        // Users management
        Route::apiResource('users', UserController::class);
        
        // Room types management
        Route::apiResource('room-types', RoomTypeController::class)->except(['index', 'show']);
        
        // Rooms management
        Route::apiResource('rooms', RoomController::class);
        
        // Bookings management
        Route::get('/bookxings', [BookingController::class, 'index']);
        
        // Services management
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        
        // Booking services management
        Route::get('/booking-services', [BookingServiceController::class, 'index']);
        Route::get('/booking-services/{id}', [BookingServiceController::class, 'show']);
        
        // Reviews management
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::get('/reviews/{id}', [ReviewController::class, 'show']);
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'dashboardSummary']);
            Route::get('/bookings', [ReportController::class, 'bookingSummary']);
            Route::get('/occupancy', [ReportController::class, 'roomOccupancy']);
            Route::get('/revenue-by-room-type', [ReportController::class, 'revenueByRoomType']);
            Route::get('/service-usage', [ReportController::class, 'serviceUsage']);
            Route::get('/customer-statistics', [ReportController::class, 'customerStatistics']);
            Route::get('/yearly-financial', [ReportController::class, 'yearlyFinancialReport']);
            Route::get('/export', [ReportController::class, 'exportReport']);
        });
    });
}); 