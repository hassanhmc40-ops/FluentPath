<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FluentPath') — English Mentor AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @yield('head')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="/" class="text-xl font-bold text-indigo-600">FluentPath</a>
            @auth
                <div class="flex items-center gap-4 text-sm">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.lessons.index') }}" class="text-gray-600 hover:text-gray-900">Admin</a>
                    @else
                        <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="/placement-test" class="text-gray-600 hover:text-gray-900">Placement Test</a>
                        <a href="/roadmap" class="text-gray-600 hover:text-gray-900">Roadmap</a>
                        <a href="/lessons" class="text-gray-600 hover:text-gray-900">Lessons</a>
                        <a href="/writing" class="text-gray-600 hover:text-gray-900">Writing</a>
                        <a href="/notifications" class="text-gray-600 hover:text-gray-900">
                            Notifications
                            @if (auth()->user()->notifications()->where('is_read', false)->exists())
                                <span class="ml-1 bg-indigo-600 text-white rounded-full px-2 py-0.5 text-xs">
                                    {{ auth()->user()->notifications()->where('is_read', false)->count() }}
                                </span>
                            @endif
                        </a>
                    @endif
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
