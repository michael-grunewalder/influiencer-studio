<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200 w-screen">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />

            {{-- User Info Header --}}
            @if($user = auth()->user())
                <div class="px-5 py-4 border-b border-base-300 flex items-center gap-3 bg-base-100/50">
                    @if($user->avatar)
                        <div class="avatar">
                            <div class="w-10 h-10 rounded-full border border-base-300">
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="object-cover rounded-full" />
                            </div>
                        </div>
                    @else
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-10 h-10">
                                <span class="text-xs font-bold">{{ $user->initials() }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm text-base-content truncate">{{ $user->name }}</h4>
                        <div class="flex gap-2 mt-0.5">
                            <a href="{{ route('profile') }}" class="text-[10px] font-bold text-slate-500 hover:text-primary hover:underline" wire:navigate>Profile</a>
                            <span class="text-[10px] text-slate-400">•</span>
                            <a href="{{ route('logout') }}" class="text-[10px] font-bold text-slate-500 hover:text-error hover:underline">Logout</a>
                        </div>
                    </div>
                </div>
            @endif

            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-home" link="/" />
                <x-menu-item title="Teams" icon="o-users" link="/teams" />
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
