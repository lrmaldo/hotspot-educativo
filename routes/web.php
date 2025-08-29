<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\TriviaHotspot;
use App\Http\Controllers\HotspotController;
use App\Livewire\Admin\HotspotStats;
use App\Livewire\Admin\TriviasManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rutas públicas del hotspot
Route::get('/hotspot', [HotspotController::class, 'show'])->name('hotspot.trivia');
Route::post('/hotspot', [HotspotController::class, 'show'])->name('hotspot.trivia.post'); // Para recibir parámetros de MikroTik
Route::post('/hotspot/connect', [HotspotController::class, 'connect'])->name('hotspot.connect');
// Ruta de vista previa (solo autenticados para evitar uso externo). También se puede usar /hotspot?preview=1
Route::middleware(['auth'])->get('/hotspot/preview', function(){
    return redirect()->route('hotspot.trivia', ['preview' => 1]);
})->name('hotspot.preview');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // Panel admin hotspot
    Route::get('admin/hotspot', HotspotStats::class)->name('admin.hotspot');
    Route::get('admin/routers', \App\Livewire\Admin\RoutersManager::class)->name('admin.routers');
    Route::get('admin/routers/{router}/login-template', \App\Http\Controllers\RouterLoginTemplateController::class)->name('admin.routers.template');
    Route::get('admin/trivias', TriviasManager::class)->name('admin.trivias');
});

require __DIR__.'/auth.php';
