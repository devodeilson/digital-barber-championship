<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChampionshipController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Webhook do gateway de pagamento
Route::post('payments/webhook', [TransactionController::class, 'webhook']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Championships
    Route::get('championships', [ChampionshipController::class, 'index']);
    Route::get('championships/{championship}', [ChampionshipController::class, 'show']);
    Route::post('championships/{championship}/join', [ChampionshipController::class, 'join']);
    Route::post('championships/{championship}/leave', [ChampionshipController::class, 'leave']);
    
    // Rotas admin de Championships
    Route::middleware('admin')->group(function () {
        Route::post('championships', [ChampionshipController::class, 'store']);
        Route::put('championships/{championship}', [ChampionshipController::class, 'update']);
        Route::delete('championships/{championship}', [ChampionshipController::class, 'destroy']);
        Route::post('championships/{championship}/activate', [ChampionshipController::class, 'activate']);
        Route::post('championships/{championship}/finish', [ChampionshipController::class, 'finish']);
    });

    // Contents
    Route::get('contents', [ContentController::class, 'index']);
    Route::post('contents', [ContentController::class, 'store']);
    Route::get('contents/{content}', [ContentController::class, 'show']);
    Route::put('contents/{content}', [ContentController::class, 'update']);
    Route::delete('contents/{content}', [ContentController::class, 'destroy']);
    Route::get('my-contents', [ContentController::class, 'myContents']);
    
    // Rotas admin de Contents
    Route::middleware('admin')->group(function () {
        Route::post('contents/{content}/approve', [ContentController::class, 'approve']);
        Route::post('contents/{content}/reject', [ContentController::class, 'reject']);
    });

    // Votes
    Route::post('contents/{content}/vote', [VoteController::class, 'store']);
    Route::put('votes/{vote}', [VoteController::class, 'update']);
    Route::delete('votes/{vote}', [VoteController::class, 'destroy']);
    Route::get('my-votes', [VoteController::class, 'myVotes']);
    Route::get('contents/{content}/votes', [VoteController::class, 'contentVotes']);

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('transactions/{transaction}/confirm', [TransactionController::class, 'confirm']);
    Route::post('transactions/{transaction}/cancel', [TransactionController::class, 'cancel']);
});

// Fallback para rotas não encontradas
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
}); 