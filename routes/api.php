<?php
use App\Enums\TokenAbility;
use App\Http\Controllers\AgreementsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IntroController;
use App\Http\Controllers\AppControlsController;
use App\Http\Controllers\SipController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\MenuItemsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CallCenterController;
use App\Http\Controllers\FileUploadController;


Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value)->group(function () {
    Route::get('/auth/refresh-token', [AuthController::class, 'refreshToken']);
});


Route::middleware('auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value)->get('/me', function (Request $request) {
    return $request->user();
});
Route::get('/', function(){
    return 'API';
});


Route::apiResource('intro', IntroController::class);
Route::apiResource('app_control', AppControlsController::class);
Route::apiResource('company', CompanyController::class);
Route::apiResource('user', UserController::class);
Route::get('/notification-settings', [NotificationController::class, 'index']);
Route::post('/notification-settings-status', [NotificationController::class, 'StatusNotificationSetting'])->middleware('auth:sanctum');
Route::post('/notifications', [NotificationController::class, 'Notification'])->middleware('auth:sanctum');
Route::post('/disable-notifications', [NotificationController::class, 'DisableNotification'])->middleware('auth:sanctum');
Route::get('/agreements', [AgreementsController::class, 'index']);
Route::get('/tour-registration', [TourController::class, 'tourRegistration']);
Route::get('/account-check', [TourController::class, 'AccountCheck']);
Route::get('/tour-detail', [TourController::class, 'tourDetail']);
Route::post('/tours', [TourController::class, 'getAllToursToGuide'])->middleware('auth:sanctum');
Route::get('/app-version-control', [AppControlsController::class, 'checkVersion']);
Route::post('/generate-room-code', [ChannelController::class, 'generateRoomCode'])->middleware('auth:sanctum');
Route::get('/room-registration', [ChannelController::class, 'roomRegistration']);
Route::post('/create-channel', [ChannelController::class, 'CreateChannel'])->middleware('auth:sanctum');
Route::apiResource('sip', SipController::class);
Route::post('/user-self', [MenuItemsController::class, 'UserSelf'])->middleware('auth:sanctum');
Route::post('/call-center', [CallCenterController::class, 'CallCenter'])->middleware('auth:sanctum');
Route::post('/send-message', [CallCenterController::class, 'SendMessage'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/disable-endpoint', [AuthController::class, 'disableEndpoint']);
Route::post('/upload', [FileUploadController::class, 'upload']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');