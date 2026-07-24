@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Register</h1>

    <form method="POST" action="/register">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">
            Register
        </button>
    </form>

    <p class="mt-4 text-sm text-center">
        Already have an account? <a href="/login" class="text-indigo-600 hover:underline">Login</a>
    </p>
</div>
@endsection
