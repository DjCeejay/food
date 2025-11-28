<?php

use App\Http\Controllers\ProfileController;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Route;

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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/admin', 'admin')->name('admin');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
