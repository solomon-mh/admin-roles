<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function deleteEvent($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

   public function update(Request $request, $id)
{
    $schedule = Schedule::findOrFail($id);

    // Ensure request data is available and correctly parsed
    $start_date = Carbon::parse($request->input('start_date'))->setTimezone('UTC');
    $end_date = Carbon::parse($request->input('end_date'))->setTimezone('UTC');

    // Debugging: Check if dates are correctly parsed
    // ::info("Updating event $id: Start date - $start_date, End date - $end_date");

    $schedule->update([
        'start' => $start_date,
        'end' => $end_date,
    ]);

    return response()->json(['message' => 'Event moved successfully']);
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
}