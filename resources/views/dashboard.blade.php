@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h1>
    <p class="text-gray-600 mb-6">Your personalized English learning journey starts here.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border rounded-lg p-4">
            <h2 class="font-semibold text-lg">Profile</h2>
            <p class="text-gray-600">Email: {{ Auth::user()->email }}</p>
            <p class="text-gray-600">Role: {{ Auth::user()->role }}</p>
            <p class="text-gray-600">Member since: {{ Auth::user()->created_at->format('M d, Y') }}</p>
        </div>
        <div class="border rounded-lg p-4">
            <h2 class="font-semibold text-lg">Quick Links</h2>
            <ul class="list-disc list-inside text-indigo-600">
                <li><a href="#" class="hover:underline">Placement Test</a></li>
                <li><a href="#" class="hover:underline">My Roadmap</a></li>
                <li><a href="#" class="hover:underline">Lessons</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
