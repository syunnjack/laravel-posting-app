<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\ThreadPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- 匿名・公開ルート ---

Route::get('/', [BoardController::class, 'index'])->name('boards.index');
Route::view('/rules', 'rules')->name('rules');
Route::view('/about', 'about')->name('about');
Route::get('/remove-request', [ReportController::class, 'createRemovalRequest'])->name('reports.remove-request');
Route::post('/remove-request', [ReportController::class, 'storeRemovalRequest'])->name('reports.remove-request.store')->middleware('throttle:5,1');
Route::get('/sitemap.xml', [BoardController::class, 'sitemap'])->name('sitemap');

Route::prefix('boards')->group(function () {
    Route::get('/{board:slug}', [BoardController::class, 'show'])->name('boards.show');
    Route::get('/{board:slug}/new', [ThreadController::class, 'create'])->name('threads.create');
    Route::post('/{board:slug}/threads', [ThreadController::class, 'store'])->name('threads.store')->middleware('throttle:10,1');
    Route::get('/{board:slug}/threads/{thread}', [ThreadController::class, 'show'])->name('threads.show')->scopeBindings();
    Route::post('/{board:slug}/threads/{thread}/replies', [ThreadPostController::class, 'store'])->name('thread-posts.store')->middleware('throttle:20,1')->scopeBindings();
    Route::post('/{board:slug}/threads/{thread}/report', [ReportController::class, 'quickReport'])->name('reports.quick')->middleware('throttle:10,1')->scopeBindings();
});

// --- モデレーション（運営者のみ） ---

Route::middleware(['auth', 'admin'])->prefix('moderation')->name('moderation.')->group(function () {
    Route::get('/', [ModerationController::class, 'index'])->name('index');
    Route::patch('/threads/{thread}/lock', [ModerationController::class, 'lockThread'])->name('threads.lock');
    Route::delete('/threads/{thread}', [ModerationController::class, 'destroyThread'])->name('threads.destroy');
    Route::delete('/thread-posts/{threadPost}', [ModerationController::class, 'destroyPost'])->name('thread-posts.destroy');
    Route::patch('/reports/{report}/resolve', [ModerationController::class, 'resolveReport'])->name('reports.resolve');
});

// --- Breezeスケルトン（運営者ログイン専用） ---

Route::get('/dashboard', function () {
    return redirect()->route('moderation.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
