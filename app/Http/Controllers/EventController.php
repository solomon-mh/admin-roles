<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        if ($request->ajax()) {
        // Your logic for storing the event
        $item = new Event();
        $item->title = $request->title;
        $item->description = $request->description;
        $item->event_date = $request->event_date;
        $item->save();

        return response()->json($item);
    }
        return redirect('/');
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json(['error' => 'An error occurred while saving the event.'], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
    public function getEvents(){
        $items = Event::all();
        return response()->json($items);
    }
    public function getEvent($id){
        $event = Event::findOrFail($id);
        return response()->json($event);
    }
}
