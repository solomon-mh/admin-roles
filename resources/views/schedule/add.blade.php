@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg">
        <form action="{{ URL('/create-schedule') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input type="text" id="title" name="title" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label for="start" class="block text-sm font-medium text-gray-700">{{ __('Start') }}</label>
                <input type="date" id="start" name="start" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required value="{{ now()->toDateString() }}">
            </div>

            <div>
                <label for="end" class="block text-sm font-medium text-gray-700">{{ __('End') }}</label>
                <input type="date" id="end" name="end" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required value="{{ now()->toDateString() }}">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="4" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">{{ __('Color') }}</label>
                <input type="color" id="color" name="color" class="mt-1 block w-24 h-20 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
