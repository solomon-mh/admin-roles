<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;




// Start Full Calender=================================================================
// Schedule
Route::get('/', [ScheduleController::class, 'index'])->middleware('auth');
Route::get('/schedules', [ScheduleController::class, 'getSchedules']);
Route::get('/schedule/{id}', [ScheduleController::class, 'getSchedule']);
Route::get('/schedules/check', [ScheduleController::class, 'checkDate']);
Route::get('/schedule/delete/{id}', [ScheduleController::class, 'deleteSchedule']);
Route::post('/schedule/{id}', [ScheduleController::class, 'update']);
Route::post('/schedule/{id}/resize', [ScheduleController::class, 'resize']);
Route::get('/schedules/search', [ScheduleController::class, 'search']);
Route::view('add-schedule', 'schedule.add');
Route::post('create-schedule', [ScheduleController::class, 'create']);
// Events
Route::post('/events/add',[EventController::class,'store']);
Route::get('/events',[EventController::class,'getEvents']);
Route::get('/events/{id}',[EventController::class,'getEvent'])->name('get-event');
Route::get('/test', function(){
    $sample = ['id'=>124, 'title'=>'Hello There','event_date'=>'12-12-2012'];
    // return response('Test Route!');
    return response()->json($sample);
});

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
