<aside
    x-data="{ collapsed: localStorage.getItem('sidebar-collapsed') === 'true' }"
    x-init="$watch('collapsed', val => localStorage.setItem('sidebar-collapsed', val))"
    class="relative flex transition-all duration-300 ease-in-out border-r border-black h-screen menu-aside"
    :class="collapsed ? 'w-12' : 'w-72'"
>
    {{-- 1. Der Menü-Inhalt --}}
    <div x-show="!collapsed" x-transition.opacity.duration.300ms class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden bg-base-200 menu-container">
        <div class="p-4 text-white font-bold text-lg tracking-widest border-b border-bearny-codes-border shrink-0">
            <span class="text-blue-500">E</span> OPUS
        </div>

        <nav class="flex flex-col bg-base-100/90">
            {{-- Hier kannst du deine Items definieren --}}
            <x-navigation.side-nav-item label="Startseite" icon="o-home" link="/" :active="Request::is('/')" />
            <x-navigation.side-nav-item label="Adressverwaltung" icon="o-users" link="/addresses" :active="Request::is('addresses*')" />
            <x-navigation.side-nav-item label="Kontaktanfragen" icon="o-envelope" />
            <x-navigation.side-nav-item label="Katalogverwaltung" icon="o-book-open" />
            <x-navigation.side-nav-item label="PIN-Verwaltung" icon="o-key" />

            <div class="p-4 pt-8 text-[10px] uppercase tracking-widest text-gray-600 font-bold border-b border-bearny-codes-border">System</div>

            <x-navigation.side-nav-item label="Admin-Bereich" icon="o-shield-check" />
            <x-navigation.side-nav-item label="Ausloggen" icon="o-power" />
        </nav>
    </div>

    {{-- 2. Der permanente Menü-Streifen (Toggle) --}}
    <div
        @click="collapsed = !collapsed"
        class="menu-trigger"
    >
        <span class="vertical-text text-[10px] tracking-[0.3em] font-bold text-primary opacity-50 uppercase">Menü</span>

        <div class="my-4">
            <div x-show="collapsed">
                <x-icon name="o-chevron-right" class="w-5 h-5 text-primary" />
            </div>
            <div x-show="!collapsed">
                <x-icon name="o-chevron-left" class="w-5 h-5 text-primary" />
            </div>
        </div>

        <span class="vertical-text text-[10px] tracking-[0.3em] font-bold text-primary opacity-50 uppercase">Menü</span>
    </div>
</aside>