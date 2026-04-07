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
            <x-navigation.side-nav-item label="Startseite" icon="house-line" link="/" :active="Request::is('/')" />

            <!-----------LOGOUT BUTTON------------------->
            <a href="#" class='bearny-codes-menu-item group text-gray-400' onclick="event.preventDefault(); document.getElementById('frmLogout').submit();">
                <div class="flex items-center gap-3">
                    <x-phosphor-sign-out class="w-4 h-4 group-hover:text-accent" />
                    <span class="text-sm transition-transform duration-200 group-hover:translate-x-1">Logout</span>
                </div>
            </a>
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