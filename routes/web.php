<?php

use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\CourseSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\LearningController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'storeContact'])->name('contact.store');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::post('/stripe/webhook', [CheckoutController::class, 'webhook'])->name('stripe.webhook');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', AdminCourseController::class)->except(['show']);
    Route::get('/courses/{course}/structure', [AdminCourseController::class, 'structure'])->name('courses.structure');
    Route::post('/courses/{course}/reorder', [AdminCourseController::class, 'reorderSections'])->name('courses.reorder');
    Route::resource('sections', CourseSectionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('lessons', LessonController::class)->only(['store', 'update', 'destroy']);
    Route::post('/lessons/{lesson}/attachments', [LessonController::class, 'uploadAttachment'])->name('lessons.attachments.store');
    Route::delete('/attachments/{attachment}', [LessonController::class, 'deleteAttachment'])->name('attachments.destroy');
    Route::get('/students', [DashboardController::class, 'students'])->name('students');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/courses/{course:slug}/checkout', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('/checkout/{order}/test', [CheckoutController::class, 'test'])->name('checkout.test');
    Route::post('/checkout/{order}/test/complete', [CheckoutController::class, 'completeTest'])->name('checkout.test.complete');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::prefix('dashboard')->name('student.')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/my-courses', [StudentDashboardController::class, 'myCourses'])->name('my-courses');
        Route::get('/orders', [StudentDashboardController::class, 'orders'])->name('orders');
        Route::get('/learn/{course:slug}', [LearningController::class, 'course'])->name('learn.course');
        Route::get('/learn/{course:slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('learn.lesson');
        Route::get('/lesson/{lesson}', [\App\Http\Controllers\Student\LessonController::class, 'show'])->name('lesson.show');
        Route::post('/lessons/{lesson}/complete', [LearningController::class, 'complete'])->name('lessons.complete');
        Route::post('/lessons/{lesson}/track-watch', [\App\Http\Controllers\Student\LessonController::class, 'trackWatch'])->name('lessons.track-watch');
        Route::get('/attachments/{attachment}/download', [LearningController::class, 'download'])->name('attachments.download');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    });

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
