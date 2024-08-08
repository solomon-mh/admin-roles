<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    //
    public function index()
    {
        return view('schedule.index');
    }

    public function create(Request $request)
    {
        $item = new Schedule();
        $item->title = $request->title;
        $item->start = $request->start;
        $item->end = $request->end;
        $item->description = $request->description;
        $item->color = $request->color;
        $item->save();

        return redirect('/');
    }


    public function getEvents()
    {
        $schedules = Schedule::all();
        return response()->json($schedules);
    }

    public function getEvent($date){
        $schedule = Schedule::findOrFail($date);
        return response()->json($schedule);
    }

    public function deleteEvent($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

   public function update(Request $request, $id)
{
    try {
        $schedule = Schedule::findOrFail($id);
        
        // Debugging: Log request data
        // Log::info('Update request data: ', $request->all());

        // Parse and update dates
        $start_date = Carbon::parse($request->input('start_date'))->setTimezone('UTC');
        $end_date = Carbon::parse($request->input('end_date'))->setTimezone('UTC');

        $schedule->update([
            'start' => $start_date,
            'end' => $end_date,
        ]);
        return response()->json(['message' => 'Event moved successfully']);
    } catch (\Exception $e) {
        // Log error and return response
        Log::error("Error updating event $id: " . $e->getMessage());
        return response()->json(['message' => 'Error updating event'], 500);
    }
}



   public function resize(Request $request, $id)
{
    $schedule = Schedule::findOrFail($id);

    $newEndDate = Carbon::parse($request->input('end_date'))->setTimezone('UTC');
    $schedule->update(['end' => $newEndDate]);

    return response()->json(['message' => 'Event resized successfully.']);
}


public function search(Request $request)
{
    $searchKeywords = $request->input('title');
    $matchingEvents = Schedule::where('title', 'like', '%' . $searchKeywords . '%')->get();
    return response()->json($matchingEvents);
}

    public function checkDate(Request $request)
{
    $date = $request->input('date');
    $event = Schedule::whereDate('start', $date)->orWhereDate('end', $date)->first();

    if ($event) {
        return response()->json([
            'hasEvent' => true,
            'event' => $event
        ]);
    } else {
        return response()->json([
            'hasEvent' => false,
            'event'=>null,
        ]);
    }
}
}