<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;




// Start Full Calender=================================================================
Route::get('/', [ScheduleController::class, 'index'])->middleware('auth');
Route::get('/schedules', [ScheduleController::class, 'getSchedules']);
Route::get('/schedule/{id}', [ScheduleController::class, 'getSchedule']);
Route::get('/schedules/check', [ScheduleController::class, 'checkDate']);
// Route::get('/schedules/check', function () {
//     return 'Route is working';
// });

Route::get('/schedule/delete/{id}', [ScheduleController::class, 'deleteSchedule']);
Route::post('/schedule/{id}', [ScheduleController::class, 'update']);
Route::post('/schedule/{id}/resize', [ScheduleController::class, 'resize']);
Route::get('/schedules/search', [ScheduleController::class, 'search']);
Route::view('add-schedule', 'schedule.add');
Route::post('create-schedule', [ScheduleController::class, 'create']);
// End Full Calender=================================================================













Route::get('/admin',function(){
 return response('admin');
})->middleware('auth');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
