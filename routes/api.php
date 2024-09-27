<?php
use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IntroController;
use App\Http\Controllers\SipController;
use App\Http\Controllers\ChannelController;

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
Route::apiResource('sip', SipController::class);
Route::apiResource('channel', ChannelController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/disable-endpoint', [AuthController::class, 'disableEndpoint']);
Route::post('/login', [AuthController::class, 'login']);