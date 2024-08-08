@extends('layouts.app')

@section('head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Task Adder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
    @if (session()->has('delete-msg'))
        {
        <p>{{ session('message') }}</p>
        }
    @endif
    <div class="container mx-auto mt-5">
        {{-- For Search --}}
        <div class="flex flex-wrap mb-6">
            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                <div class="flex">
                    <input type="text" id="searchInput"
                        class="flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Search events">

                    <button id="searchButton"
                        class="btn btn btn bg-lime-300 px-3 py-2 rounded-lg rounded-l-none hover:bg-lime-500 btn-success">{{ __('Search') }}</button>
                </div>
            </div>
            <div class="w-full md:w-1/2 flex justify-end space-x-2">
                <button id="exportButton"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{ __('Export Calendar') }}</button>
                <button id="openModal" class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">
                    {{ __('Add Event') }}
                </button>

                <a href="{{ URL('add-schedule') }}"
                    class="btn btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{ __('Add Task') }}</a>
            </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-4">
            <div id="calendar" class="w-3/4 mx-auto h-screen"></div>
        </div>
        <div id="eventModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 transition-opacity opacity-0 pointer-events-none">
            <div id="eventModalContent"
                class="bg-white rounded-lg shadow-md max-w-lg w-full mx-4 p-6 max-h-[80vh] flex flex-col relative overflow-y-auto">
                <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <h2 id="singleEventTitle" class="text-xl font-semibold mb-4">Event Title</h2>
                <p id="eventStartDate" class="text-gray-700">Event Date</p>
                <p id="eventEndDate" class="text-gray-700">Event Date</p>
                <p id="eventDescription" class="text-gray-600">Event Description</p>
            </div>
        </div>
    </div>
    {{-- Schedule Modal --}}
    <x-schedule-modal />


    {{-- <script src="../../js/schedule-modal.js"></script> --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
@endsection
