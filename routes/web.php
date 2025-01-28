<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChampionshipController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemTextController;
use App\Http\Controllers\Admin\ReportController;

// Rotas públicas
Route::get('/', function () {
    return view('welcome');
});

// Rotas de autenticação
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rotas do Admin
Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'admin']
], function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Usuários
    Route::resource('users', UserController::class);

    // Campeonatos
    Route::resource('championships', ChampionshipController::class);

    // Configurações
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Textos do Sistema
    Route::get('system-texts', [SystemTextController::class, 'index'])->name('system-texts.index');
    Route::get('system-texts/create', [SystemTextController::class, 'create'])->name('system-texts.create');
    Route::post('system-texts', [SystemTextController::class, 'store'])->name('system-texts.store');
    Route::put('system-texts/{systemText}', [SystemTextController::class, 'update'])->name('system-texts.update');
    Route::delete('system-texts/{systemText}', [SystemTextController::class, 'destroy'])->name('system-texts.destroy');
    Route::post('system-texts/batch-update', [SystemTextController::class, 'batchUpdate'])
        ->name('system-texts.batch-update');

    // Relatórios
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
});
