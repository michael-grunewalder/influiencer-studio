<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="sunset">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'eOPUS Tool' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-300">

<div class="flex h-screen overflow-hidden">

    {{-- Unsere neue Komponente --}}
    <x-navigation.side-navigation />

    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto relative">
        <div class="p-6">
            {{ $content ?? $slot }}
        </div>
    </main>
</div>

<x-toast />
<form action="{{route('logout')}}" method="post" id="frmLogout">
    @csrf
</form>
</body>
</html>