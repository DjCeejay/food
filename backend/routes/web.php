<?php

use App\Http\Controllers\ProfileController;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Route;

// Serve static assets directly
Route::get('/styles.css', function () {
    return response()->file(public_path('styles.css'), ['Content-Type' => 'text/css']);
});

Route::get('/script.js', function () {
    return response()->file(public_path('script.js'), ['Content-Type' => 'application/javascript']);
});

Route::get('/assets/{file}', function ($file) {
    $publicPath = public_path('assets/' . $file);
    if (file_exists($publicPath) && is_file($publicPath)) {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
        ];
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        return response()->file($publicPath, ['Content-Type' => $mimeType]);
    }
    return abort(404);
})->where('file', '.*');

Route::get('/', function () {
    $categories = Category::orderBy('sort_order')->orderBy('name')->get();
    $menuItems = MenuItem::with('category')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    $featured = $menuItems->take(3);

    return view('home', compact('categories', 'menuItems', 'featured'));
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'active', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/admin', 'admin')->middleware(['active', 'role:admin'])->name('admin');
    Route::view('/pos', 'pos')->middleware(['active', 'role:admin|pos'])->name('pos');
    Route::view('/kitchen', 'kitchen')->middleware(['active', 'role:admin|kitchen'])->name('kitchen');
    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('active')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('active')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('active')->name('profile.destroy');
});

require __DIR__.'/auth.php';
